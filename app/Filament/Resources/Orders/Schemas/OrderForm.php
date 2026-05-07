<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\OrderStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->required()
                    ->searchable()
                    ->disabled(),

                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->rule('decimal:0,2')
                    ->prefix('$')
                    ->disabled(),

                Select::make('status')
                    ->required()
                    ->options([
                        'pending' => ucfirst(OrderStatus::Pending->value),
                        'paid' => ucfirst(OrderStatus::Paid->value),
                        'cancelled' => ucfirst(OrderStatus::Cancelled->value),
                        'failed' => ucfirst(OrderStatus::Failed->value),
                        'shipped' => ucfirst(OrderStatus::Shipped->value),
                        'delivered' => ucfirst(OrderStatus::Delivered->value),
                    ])
                    ->default('pending'),
            ]);
    }
}
