<?php
declare(strict_types=1);

namespace CrudConnect\Navigation;

final class FormRedirect
{
    /** Only accept a local referrer to the exact listing path. */
    public static function listingReferrer(?string $referer, string $indexUrl): ?string
    {
        if (!$referer || !str_starts_with($referer, '/') || str_starts_with($referer, '//')) {
            return null;
        }
        $parts = parse_url($referer);
        if (
            $parts === false || isset($parts['host']) || isset($parts['scheme'])
            || ($parts['path'] ?? '') !== $indexUrl
        ) {
            return null;
        }

        return $referer;
    }

    /** @return array|string */
    public static function target(string $action, mixed $mode, mixed $id, array|string $returnUrl): array|string
    {
        $fallback = $action === 'edit' ? 'stay' : 'add';
        $allowed = $action === 'edit' ? ['stay', 'index'] : ['add', 'edit', 'index'];
        if (!is_string($mode) || !in_array($mode, $allowed, true)) {
            $mode = $fallback;
        }
        if ($mode === 'index') {
            return $returnUrl;
        }
        if ($mode === 'add') {
            return ['action' => 'add'];
        }
        if ($id === null || $id === '') {
            return ['action' => 'index'];
        }

        return ['action' => 'edit', $id];
    }
}
