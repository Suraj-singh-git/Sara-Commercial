<?php

namespace App\Services\Analytics;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;

class AnalyticsService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return array{from: ?CarbonImmutable, to: ?CarbonImmutable, revenue: float, gross: float, orders: int, active_users: int}
     */
    public function summary(string $range = 'monthly'): array
    {
        [$from, $to] = $this->resolveRange($range);

        return [
            'from' => $from,
            'to' => $to,
            'revenue' => $this->orders->aggregateRevenue($from?->toDateTime(), $to?->toDateTime()),
            'gross' => $this->orders->aggregateGrossValue($from?->toDateTime(), $to?->toDateTime()),
            'orders' => $this->orders->countOrders($from?->toDateTime(), $to?->toDateTime()),
            'active_users' => $this->orders->countActiveUsers($from?->toDateTime(), $to?->toDateTime()),
        ];
    }

    /**
     * @return list<array{label: string, revenue: float, gross: float, orders: int}>
     */
    public function series(string $range = 'monthly'): array
    {
        return match ($range) {
            'daily' => $this->dailySeries(),
            'weekly' => $this->weeklySeries(),
            default => $this->monthlySeries(),
        };
    }

    /**
     * @return array{0: ?CarbonImmutable, 1: ?CarbonImmutable}
     */
    private function resolveRange(string $range): array
    {
        $now = CarbonImmutable::now();

        return match ($range) {
            'daily' => [$now->subDays(6)->startOfDay(), $now->endOfDay()],
            'weekly' => [$now->subWeeks(7)->startOfWeek(), $now->endOfWeek()],
            default => [$now->subMonths(5)->startOfMonth(), $now->endOfMonth()],
        };
    }

    /**
     * @return list<array{label: string, revenue: float, gross: float, orders: int}>
     */
    private function dailySeries(): array
    {
        $now = CarbonImmutable::now();
        $points = [];

        for ($i = 6; $i >= 0; $i--) {
            $from = $now->subDays($i)->startOfDay();
            $to = $from->endOfDay();

            $points[] = [
                'label' => $from->format('d M'),
                'revenue' => $this->orders->aggregateRevenue($from->toDateTime(), $to->toDateTime()),
                'gross' => $this->orders->aggregateGrossValue($from->toDateTime(), $to->toDateTime()),
                'orders' => $this->orders->countOrders($from->toDateTime(), $to->toDateTime()),
            ];
        }

        return $points;
    }

    /**
     * @return list<array{label: string, revenue: float, gross: float, orders: int}>
     */
    private function weeklySeries(): array
    {
        $now = CarbonImmutable::now();
        $points = [];

        for ($i = 7; $i >= 0; $i--) {
            $from = $now->subWeeks($i)->startOfWeek();
            $to = $from->endOfWeek();

            $points[] = [
                'label' => 'W'.$from->format('W').' '.$from->format('d M'),
                'revenue' => $this->orders->aggregateRevenue($from->toDateTime(), $to->toDateTime()),
                'gross' => $this->orders->aggregateGrossValue($from->toDateTime(), $to->toDateTime()),
                'orders' => $this->orders->countOrders($from->toDateTime(), $to->toDateTime()),
            ];
        }

        return $points;
    }

    /**
     * @return list<array{label: string, revenue: float, gross: float, orders: int}>
     */
    private function monthlySeries(): array
    {
        $now = CarbonImmutable::now();
        $points = [];

        for ($i = 5; $i >= 0; $i--) {
            $from = $now->subMonths($i)->startOfMonth();
            $to = $from->endOfMonth();

            $points[] = [
                'label' => $from->format('M Y'),
                'revenue' => $this->orders->aggregateRevenue($from->toDateTime(), $to->toDateTime()),
                'gross' => $this->orders->aggregateGrossValue($from->toDateTime(), $to->toDateTime()),
                'orders' => $this->orders->countOrders($from->toDateTime(), $to->toDateTime()),
            ];
        }

        return $points;
    }
}
