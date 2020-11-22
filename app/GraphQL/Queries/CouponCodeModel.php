<?php

namespace App\GraphQL\Queries;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

use App\CouponCode;

class CouponCodeModel
{
    public function get_active_coupon_codes($rootValue, array $args)
    {
        return CouponCode::notExpired()->inQuantity()->get();
    }

    public function get_coupon_code_if_valid($rootValue, array $args)
    {
        return CouponCode::whereCode($args['code'])->notExpired()->inQuantity()->first();
    }
}
