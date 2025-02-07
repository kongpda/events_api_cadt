<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory(100)->create()->each(function ($order): void {
            $tickets = Ticket::where('event_id', $order->event_id)
                ->where('order_id', null)
                ->take($order->quantity)
                ->get();

            foreach ($tickets as $ticket) {
                $ticket->update(['order_id' => $order->id]);
            }
        });
    }
}
