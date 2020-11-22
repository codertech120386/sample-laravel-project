<?php

namespace App;

use ScoutElastic\SearchRule;

class SearchWorkspaceRule extends SearchRule
{
    /**
     * @inheritdoc
     */
    public function buildHighlightPayload()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function buildQueryPayload()
    {
        $query = $this->builder->query;

        return [
            'should' => [
                [
                    'match' => [
                        'name' => [
                            'query' => $query,
                            'boost' => 10
                        ]
                    ]
                ],
                [
                    'match' => [
                        'type' => [
                            'query' => $query,
                            'boost' => 5
                        ]
                    ]
                ],
                [
                    'match' => [
                        'per_day' => [
                            'query' => $query,
                            'boost' => 5
                        ]
                    ]
                ],
                [
                    'match' => [
                        'space_types' => [
                            'query' => $query,
                            'boost' => 4
                        ]
                    ]
                ],
                [
                    'match' => [
                        'seats' => [
                            'query' => $query,
                            'boost' => 4
                        ]
                    ]
                ],
                [
                    'match' => [
                        'amenities' => [
                            'query' => $query,
                            'boost' => 4
                        ]
                    ]
                ],
                [
                    'match' => [
                        'address' => [
                            'query' => $query,
                            'boost' => 3
                        ]
                    ]
                ],
                [
                    'match' => [
                        'description' => [
                            'query' => $query,
                            'boost' => 1
                        ]
                    ]
                ]
            ]
        ];
    }
}
