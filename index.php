<?php

require_once "vendor/autoload.php";

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;

class Service
{
    private $token;
    private $client;

    public function __construct($token)
    {
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => 'https://my.aeza.net/api/',
        ]);
    }

    private function query($options)
    {
        try {
            $response = $this->client->request($options['method'], $options['route'], [
                'headers' => [
                    'X-API-Key' => $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $options['data'] ?? []
            ]);

            $data = json_decode($response->getBody(), true);
            if(isset($data['error'])){
                return ['data' => null, 'error' => $data['error']];
            }
            return ['data' => $data['data'], 'error' => null];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);
                return ['data' => null, 'error' => $errorResponse];
            }
            return ['data' => null, 'error' => [
                'slug' => 'network_error',
                'message' => $e->getMessage(),
                'data' => []
            ]];
        }
    }

    public function osList()
    {
        $result = $this->query([
            'method' => 'GET',
            'route' => 'os'
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => $result['data']['items']
            ]
        ];
    }

    public function products($id = null)
    {
        $route = 'services/products' . ($id ? '/' . $id : '');
        $result = $this->query([
            'method' => 'GET',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => array_map(function ($item) {
                    return $item ? [
                        'id' => $item['id'],
                        'title' => $item['name'],
                        'oslist' => $item['payload']['oslist'] ?? null,
                        'prices' => $item['prices']
                    ] : [];
                }, $result['data']['items'])
            ]
        ];
    }

    public function getServer($id)
    {
        $route = 'services' . ($id ? '/' . $id : '');
        $result = $this->query([
            'method' => 'GET',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'total' => $result['data']['total'],
            'response' => $result['data']['total'] > 1 ? $result['data']['items'] : $result['data']['items'][0]
        ];
    }

    public function getTask($id)
    {
        $route = "services/{$id}/tasks";
        $result = $this->query([
            'method' => 'GET',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => $result['data']['items']
            ]
        ];
    }

    public function getCharts($id)
    {
        $route = "services/{$id}/charts";
        $result = $this->query([
            'method' => 'POST',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => $result['data']['items']
            ]
        ];
    }

    public function ctl($params)
    {
        $route = "services/{$params['id']}/ctl";
        $result = $this->query([
            'method' => 'POST',
            'route' => $route,
            'data' => [
                'action' => $params['action']
            ]
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => $result['data']['items']
            ]
        ];
    }

    public function changePassword($params)
    {
        $route = "services/{$params['id']}/changePassword";
        $result = $this->query([
            'method' => 'POST',
            'route' => $route,
            'data' => ['password' => $params['password']]
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => $result['data']
        ];
    }

    public function reinstall($params)
    {
        $route = "services/{$params['id']}/reinstall";
        $result = $this->query([
            'method' => 'POST',
            'route' => $route,
            'data' => [
                'os' => $params['os'],
                'recipe' => $params['recipe'],
                'password' => $params['password']
            ]
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'status' => $result['data']['status'] ?? 'error',
                'items' => $result['data']['items'] ?? []
            ]
        ];
    }

    public function deleteServer($id)
    {
        $route = "services/{$id}";
        $result = $this->query([
            'method' => 'DELETE',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => $result['data']
        ];
    }

    public function createServer($params)
    {
        $defaultParameters = [
            'recipe' => null,
            'os' => 940,
            'isoUrl' => ''
        ];

        $body = [
            'count' => 1,
            'method' => 'balance',
            'parameters' => $params['parameters'] ?? $defaultParameters
        ];

        $body = array_merge($body, $params);

        $route = 'services/orders';
        $result = $this->query([
            'method' => 'POST',
            'route' => $route,
            'data' => $body
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'total' => count($result['data']['items']),
            'response' => [
                'items' => $result['data']['items'],
                'transaction' => $result['data']['transaction']
            ]
        ];
    }

    public function getOrder($orderId)
    {
        $route = "services/orders/{$orderId}";
        $result = $this->query([
            'method' => 'GET',
            'route' => $route
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'total' => count($result['data']['items']),
            'response' => [
                'items' => $result['data']['items'],
                'transaction' => $result['data']['transaction']
            ]
        ];
    }
    public function profileGet() {
        $result = $this->query([
            'method' => 'GET',
            'route' => 'accounts?current=1&edit=true&extra=1'
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'total' => $result['data']['total'],
            'response' => $result['data']['total'] > 1 ? $result['data']['items'] : $result['data']['items'][0]
        ];
    }

    public function profileLimits() {
        $result = $this->query([
            'method' => 'GET',
            'route' => 'services/limits'
        ]);
        return $result['error'] ? [
            'slug' => $result['error']['slug'],
            'message' => $result['error']['message'],
            'data' => $result['error']['data'] ?? []
        ] : [
            'response' => [
                'items' => array_map(function ($item) {
                    return [
                        'title' => $item['name'],
                        'groups' => $item['groups'],
                        'available' => $item['available'],
                        'used' => $item['used']
                    ];
                }, $result['data']['items'])
            ]
        ];
    }

}


//var_dump((new Service('c802375027b448787792b3ac29426f04'))->profileGet());


// $res = (new Service('89dbab6e53b8827de26a9e6789607e4e'))->createServer([
//     "productId"=> 3,
//     "term"=>'hour',
//     "autoProlong"=> "false",
//     "name"=> 'test-name',
  
// ]);

var_dump((new Service('c802375027b448787792b3ac29426f04'))->products());

//var_dump($res);