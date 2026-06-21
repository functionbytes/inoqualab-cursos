<?php

namespace Tests\Unit;

use Tests\TestCase;

class CartCouponDiscountTest extends TestCase
{
    private function coupon(array $attrs): object
    {
        return (object) array_merge([
            'type' => 1,        // 1 = porcentaje, 0 = monto fijo
            'amount' => 0,
            'course_ids' => null,
            'bundle_ids' => null,
        ], $attrs);
    }

    private function lines(): array
    {
        return [
            ['type' => 'course', 'id' => 10, 'amount' => 100000.0],
            ['type' => 'course', 'id' => 20, 'amount' => 50000.0],
            ['type' => 'bundle', 'id' => 5, 'amount' => 200000.0],
        ];
    }

    public function test_percentage_coupon_without_restriction_applies_to_all_lines(): void
    {
        $coupon = $this->coupon(['type' => 1, 'amount' => 10]);
        // 10% de 350000 = 35000
        $this->assertEqualsWithDelta(35000, cartCouponDiscount($this->lines(), $coupon), 0.01);
    }

    public function test_fixed_coupon_is_capped_at_eligible_subtotal(): void
    {
        $coupon = $this->coupon(['type' => 0, 'amount' => 999999999]);
        // monto fijo enorme -> tope = subtotal elegible (350000)
        $this->assertEqualsWithDelta(350000, cartCouponDiscount($this->lines(), $coupon), 0.01);
    }

    public function test_coupon_restricted_to_one_course_applies_only_to_that_line(): void
    {
        $coupon = $this->coupon(['type' => 1, 'amount' => 50, 'course_ids' => '10']);
        // 50% solo sobre la línea del curso 10 (100000) = 50000
        $this->assertEqualsWithDelta(50000, cartCouponDiscount($this->lines(), $coupon), 0.01);
    }

    public function test_coupon_with_no_eligible_lines_returns_zero(): void
    {
        $coupon = $this->coupon(['type' => 1, 'amount' => 50, 'course_ids' => '999']);
        $this->assertSame(0.0, (float) cartCouponDiscount($this->lines(), $coupon));
    }

    public function test_percentage_above_100_is_clamped_and_never_exceeds_subtotal(): void
    {
        // Dato inválido (11000%): el descuento se topa al subtotal elegible (350000),
        // evitando totales negativos/gratuitos por overflow.
        $coupon = $this->coupon(['type' => 1, 'amount' => 11000]);
        $this->assertEqualsWithDelta(350000, cartCouponDiscount($this->lines(), $coupon), 0.01);
    }

    public function test_negative_percentage_is_clamped_to_zero(): void
    {
        $coupon = $this->coupon(['type' => 1, 'amount' => -50]);
        $this->assertSame(0.0, (float) cartCouponDiscount($this->lines(), $coupon));
    }
}
