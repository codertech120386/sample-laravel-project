<?php

namespace App\GraphQL\Queries;

use App\Faq;

class FaqModel
{

    public function get_user_faqs($rootValue, array $args)
    {
        $recommended_faqs = Faq::where('is_recommended', true)->where('faq_for', 'users')->get();
        $normal_faqs = Faq::where('is_recommended', false)->where('faq_for', 'users')->get();

        return ['recommended_faqs' => $recommended_faqs, 'normal_faqs' => $normal_faqs];
    }

    public function get_space_faqs($rootValue, array $args)
    {
        $recommended_faqs = Faq::where('is_recommended', true)->where('faq_for', 'spaces')->get();
        $normal_faqs = Faq::where('is_recommended', false)->where('faq_for', 'spaces')->get();

        return ['recommended_faqs' => $recommended_faqs, 'normal_faqs' => $normal_faqs];
    }
}
