<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\WebsiteType;
use App\Models\Lead;
use App\Models\OptOut;
use App\Support\Phone;
use Illuminate\Support\Str;

class LeadQualifier
{
    public function apply(Lead $lead): void
    {
        [$lead->phone_e164, $lead->is_mobile] = Phone::normalize($lead->phone_raw);
        $lead->website_type = $this->websiteType($lead->website);
        $lead->greeting_name ??= $this->greetingName($lead->name, $lead->niche);

        if ($reason = $this->discardReason($lead)) {
            $lead->fill(['status' => LeadStatus::Descartado, 'discard_reason' => $reason, 'score' => 0, 'problem' => null]);

            return;
        }

        $lead->score = $this->score($lead);
        $lead->problem = match ($lead->website_type) {
            WebsiteType::None => 'ainda não tem um site próprio',
            WebsiteType::Social => str_contains((string) $lead->website, 'instagram.com')
                ? 'usa só o Instagram como site'
                : 'usa só as redes sociais como site',
            default => null,
        };

        $qualified = $lead->score >= config('leads.min_score');
        $lead->status = $qualified ? LeadStatus::Qualificado : LeadStatus::Descartado;
        $lead->discard_reason = $qualified ? null : 'score_baixo';
    }

    private function websiteType(?string $url): WebsiteType
    {
        if (blank($url)) {
            return WebsiteType::None;
        }

        $host = Str::of((string) parse_url($url, PHP_URL_HOST))->lower()->chopStart('www.')->toString();

        foreach (config('leads.social_domains') as $domain) {
            if ($host === $domain || str_ends_with($host, ".{$domain}")) {
                return WebsiteType::Social;
            }
        }

        return WebsiteType::Site;
    }

    /** A primeira regra que bater define o motivo. */
    private function discardReason(Lead $lead): ?string
    {
        return match (true) {
            $lead->business_status !== 'OPERATIONAL' => 'fechado',
            $lead->phone_e164 === null => 'sem_telefone',
            OptOut::where('phone_e164', $lead->phone_e164)->exists() => 'opt_out',
            ! $lead->is_mobile => 'telefone_fixo',
            default => null,
        };
    }

    private function score(Lead $lead): int
    {
        $score = match ($lead->website_type) {
            WebsiteType::None => 40,
            WebsiteType::Social => 30,
            WebsiteType::Site => 0,
        };

        $score += match (true) {
            $lead->ratings_count >= 200 => 25,
            $lead->ratings_count >= 50 => 15,
            $lead->ratings_count >= 15 => 5,
            default => 0,
        };

        if ($lead->rating >= 4.5) {
            $score += 10;
        }

        return $score;
    }

    /**
     * Palpite de nome para a saudação: "Academia | FJ Jr Academia | Blumenau/SC" → "FJ Jr Academia".
     * Descarta pedaços que são só o nicho ou "Cidade/UF". Revise no painel antes de enviar.
     */
    private function greetingName(string $name, ?string $niche): string
    {
        $parts = array_filter(
            array_map('trim', preg_split('/\s+[|\-–—·]\s+|\|/u', $name)),
            fn (string $part) => $part !== ''
                && mb_strtolower($part) !== mb_strtolower((string) $niche)
                && ! preg_match('/\/\s*[A-Z]{2}$/', $part),
        );

        return reset($parts) ?: $name;
    }
}
