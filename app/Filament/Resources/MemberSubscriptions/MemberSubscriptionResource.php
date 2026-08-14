<?php

namespace App\Filament\Resources\MemberSubscriptions;

use App\Filament\Resources\MemberSubscriptions\Pages\CreateMemberSubscription;
use App\Filament\Resources\MemberSubscriptions\Pages\EditMemberSubscription;
use App\Filament\Resources\MemberSubscriptions\Pages\ListMemberSubscriptions;
use App\Filament\Resources\MemberSubscriptions\Schemas\MemberSubscriptionForm;
use App\Filament\Resources\MemberSubscriptions\Tables\MemberSubscriptionsTable;
use App\Models\MemberSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Premium memberships held by members.
 *
 * Sits next to the provider subscriptions in the same navigation group; the two
 * are separate tables because `subscriptions` is keyed on `profile_id` and a
 * member has no profile.
 */
class MemberSubscriptionResource extends Resource
{
    protected static ?string $model = MemberSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationGroup(): ?string
    {
        return __('subscriptions.navigation.subscriptions');
    }

    public static function getNavigationLabel(): string
    {
        return __('subscriptions.member_subscription.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('subscriptions.member_subscription.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('subscriptions.member_subscription.plural');
    }

    /**
     * Memberships about to lapse are the ones an operator may want to act on.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->expiring(7)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return MemberSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberSubscriptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberSubscriptions::route('/'),
            'create' => CreateMemberSubscription::route('/create'),
            'edit' => EditMemberSubscription::route('/{record}/edit'),
        ];
    }
}
