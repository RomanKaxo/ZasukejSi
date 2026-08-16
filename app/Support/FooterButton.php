<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Setting;

/**
 * The single button in the footer's left slot.
 *
 * It used to be a hardcoded "Registrace" wrapped in @guest, so a signed-in
 * visitor lost the button — and with it the layout, because the row is
 * space-between. Both states are now configured in the admin: which page the
 * button opens and, optionally, what it says.
 *
 * The slot is never empty. If the configured page is unpublished or deleted,
 * the button falls back to its built-in behaviour rather than disappearing.
 */
class FooterButton
{
    public const KEY_GUEST_PAGE = 'footer.button.guest_page_id';
    public const KEY_GUEST_LABEL = 'footer.button.guest_label';
    public const KEY_AUTH_PAGE = 'footer.button.auth_page_id';
    public const KEY_AUTH_LABEL = 'footer.button.auth_label';

    /** The page a signed-in visitor is offered when nothing is configured. */
    public const DEFAULT_AUTH_SLUG = 'vip-premium';

    /**
     * What the footer should render for the current visitor.
     *
     * @return array{label: string, url: ?string, opensRegisterModal: bool}
     */
    public static function forCurrentVisitor(): array
    {
        return auth()->check() ? self::forMember() : self::forGuest();
    }

    /** @return array{label: string, url: ?string, opensRegisterModal: bool} */
    private static function forGuest(): array
    {
        $page = self::page(Setting::get(self::KEY_GUEST_PAGE));
        $label = self::label(Setting::get(self::KEY_GUEST_LABEL));

        // No page configured: the original behaviour, which is the sign-up
        // modal rather than a link.
        if (! $page) {
            return [
                'label' => $label ?? __('front.footer.registration'),
                'url' => null,
                'opensRegisterModal' => true,
            ];
        }

        return [
            'label' => $label ?? $page->title,
            'url' => self::url($page),
            'opensRegisterModal' => false,
        ];
    }

    /** @return array{label: string, url: ?string, opensRegisterModal: bool} */
    private static function forMember(): array
    {
        $page = self::page(Setting::get(self::KEY_AUTH_PAGE))
            ?? Page::published()->where('slug', self::DEFAULT_AUTH_SLUG)->first();

        $label = self::label(Setting::get(self::KEY_AUTH_LABEL));

        // Someone already registered cannot register again, so this state never
        // opens the sign-up modal. With no page to point at — an install that
        // has no VIP & Premium page — the account is the sensible destination.
        if (! $page) {
            return [
                'label' => $label ?? __('front.nav.myaccount'),
                'url' => route(self::accountRoute()),
                'opensRegisterModal' => false,
            ];
        }

        return [
            'label' => $label ?? $page->title,
            'url' => self::url($page),
            'opensRegisterModal' => false,
        ];
    }

    /** Same split as the navbar: men without the admin role are members. */
    private static function accountRoute(): string
    {
        $user = auth()->user();

        return $user && $user->isMale() && ! $user->hasRole('admin')
            ? 'account.member.dashboard'
            : 'account.dashboard';
    }

    private static function page(mixed $id): ?Page
    {
        if (blank($id)) {
            return null;
        }

        return Page::published()->find((int) $id);
    }

    private static function label(mixed $value): ?string
    {
        $label = trim((string) ($value ?? ''));

        return $label !== '' ? $label : null;
    }

    private static function url(Page $page): string
    {
        return url('/' . ltrim((string) $page->slug, '/'));
    }
}
