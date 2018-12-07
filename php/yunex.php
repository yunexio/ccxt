<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class yunex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'yunex',
            'name' => 'Yunex',
            'countries' => array ( 'Hong Kong' ),
            'version' => 'v1',
            'accounts' => null,
            'accountsById' => null,
            'has' => array (
                'CORS' => true,
                'fetchMarkets' => true,
                'fetchBalance' => true,
                'createOrder' => true,
                'cancelOrder' => true,
                'fetchOHLCV' => true,
                'fetchTicker' => true,
                'fetchTickers' => false,
                'fetchMyTrades' => false,
                'fetchTrades' => false,
                'fetchOrder' => true,
                'fetchOrders' => false,
                'fetchOrderBook' => true,
                'fetchOpenOrders' => false,
                'fetchClosedOrders' => false,
            ),
            'timeframes' => array (
                '1m' => '1min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '4h' => '1hour',
                '1d' => '1day',
            ),
            'urls' => array (
                'logo' => 'https://theme.zdassets.com/theme_assets/2289273/fdd2e3bf9e40a9751d199a337c48d8a48194ff7c.png',
                'api' => 'https://a.yunex.io',
                'www' => 'https://yunex.io/',
                'referral' => 'https://yunex.io/user/register?inviter=16609',
                'doc' => 'https://github.com/yunexio/openAPI',
                'fees' => 'https://support.yunex.io/hc/en-us/articles/360003486391-Fees',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'api/v1/base/coins/tradepair',
                        'api/market/depth',
                        'api/market/trade/kline',
                        'api/market/trade/info',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'api/v1/coin/balance',
                    ),
                    'post' => array (
                        'api/v1/order/buy',
                        'api/v1/order/sell',
                        'api/v1/order/cancel',
                        'api/v1/order/query',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.002,
                    'taker' => 0.002,
                ),
            ),
            'funding' => array (
                'tierBased' => false,
                'percentage' => false,
                'deposit' => array (),
                'withdraw' => array (
                    'BTC' => 0.001,
                    'ETH' => 0.01,
                    'BCH' => 0.001,
                    'LTC' => 0.01,
                    'ETC' => 0.01,
                    'USDT' => 2,
                    'SNET' => 20,
                    'KT' => 20,
                    'YUN' => 20,
                    'Rating' => 20,
                    'YBT' => 20,
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetApiV1BaseCoinsTradepair ();
        $data = $response['data'];
        $result = array ();
        for ($i = 0; $i < count ($data); $i++) {
            $market = $data[$i];
            $id = $market['symbol'];
            $symbol = $market['name'];
            $base = explode ('/', $symbol)[0];
            $quote = explode ('/', $symbol)[1];
            $baseId = $market['base_coin_id'];
            $quoteId = $market['coin_id'];
            $active = true;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'info' => $market,
            );
        }
        return $result;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'symbol' => $this->market_id($symbol),
            'price' => String ($price),
            'volume' => String ($amount),
        );
        $response = '';
        if ($side === 'buy') {
            $response = $this->privatePostApiV1OrderBuy (array_merge ($request, $params));
        } else if ($side === 'sell') {
            $response = $this->privatePostApiV1OrderSell (array_merge ($request, $params));
        }
        $data = $response['data'];
        return array (
            'info' => $response,
            'id' => $this->safe_string($data, 'order_id'),
        );
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $_symbol = $this->market_id($symbol);
        $request = array (
            'symbol' => $_symbol,
            'order_id' => $id,
        );
        $response = $this->privatePostApiV1OrderQuery (array_merge ($request, $params));
        $order = $this->parse_order($response['data'], $_symbol);
        return $order;
    }

    public function parse_side ($sideId) {
        if ($sideId === 1) {
            return 'buy';
        } else if ($sideId === 2) {
            return 'sell';
        } else {
            return null;
        }
    }

    public function parse_order ($order, $symbol) {
        $id = $this->safe_string($order, 'order_id');
        $timestamp = $this->safe_float($order, 'timestamp');
        $sideId = $this->safe_integer($order, 'type');
        $side = $this->parse_side ($sideId);
        $type = null;
        $price = $this->safe_float($order, 'price');
        $average = null;
        $amount = $this->safe_float($order, 'volume');
        $filled = $this->safe_float($order, 'trade_volume');
        $remaining = null;
        if ($amount && $filled) {
            $remaining = $amount - $filled;
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $cost = null;
        $fee = null;
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
        return $result;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '1' => 'open',
            '2' => 'closed',
            '3' => 'canceled',
            '4' => 'lose',
        );
        return (is_array ($statuses) && array_key_exists ($status, $statuses)) ? $statuses[$status] : $status;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'order_id' => $id,
        );
        if ($symbol !== null) {
            $request['symbol'] = $this->market_id($symbol);
        }
        $results = $this->privatePostApiV1OrderCancel (array_merge ($request, $params));
        $success = $results['ok'] === 1;
        $returnVal = array ( 'info' => $results, 'success' => $success );
        return $returnVal;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        $request = array (
            'symbol' => $marketId,
        );
        if ($limit !== null) {
            $request['level'] = $limit;
        }
        $response = $this->publicGetApiMarketDepth (array_merge ($request, $params));
        $data = $response['data'];
        $timestamp = null;
        $datetime = null;
        $data['timestamp'] = $timestamp;
        $data['datetime'] = $datetime;
        return $data;
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv['ts'] * 1000,
            $ohlcv['v'][0],
            $ohlcv['v'][2],
            $ohlcv['v'][3],
            $ohlcv['v'][4],
            $ohlcv['v'][5],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 300, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'type' => $this->timeframes[$timeframe],
        );
        if ($limit !== null) {
            $request['count'] = $limit;
        }
        $response = $this->publicGetApiMarketTradeKline (array_merge ($request, $params));
        // return $response
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->markets[$symbol];
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->publicGetApiMarketTradeInfo (array_merge ($request, $params));
        $data = $response['data'];
        $timestamp = $this->safe_integer($data, 'ts');
        $timestamp = $timestamp * 1000;
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($data, 'max_price'),
            'low' => $this->safe_float($data, 'min_price'),
            'bid' => null,
            'bidVolume' => null,
            'ask' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => $this->safe_float($data, 'open_price'),
            'close' => $this->safe_float($data, 'close_price'),
            'last' => $this->safe_float($data, 'close_price'),
            'previousClose' => null,
            'change' => $this->safe_float($data, 'rate'),
            'percentage' => null,
            'average' => null,
            'baseVolume' => $this->safe_float($data, 'volume'),
            'quoteVolume' => null,
            'info' => $response,
        );
    }

    public function fetch_balance () {
        $response = $this->privateGetApiV1CoinBalance ();
        $data = $response['data']['coin'];
        $result = array ( 'info' => $response );
        for ($i = 0; $i < count ($data); $i++) {
            $balance = $data[$i];
            $currency = $balance['name'];
            $account = null;
            if (is_array ($result) && array_key_exists ($currency, $result))
                $account = $result[$currency];
            else
                $account = $this->account ();
            $result[$currency] = $account;
            $result[$currency]['used'] = floatval ($balance['freezed']);
            $result[$currency]['free'] = floatval ($balance['usable']);
            $result[$currency]['total'] = floatval ($balance['total']);
        }
        return $this->parse_balance($result);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
            $headers = array (
                'Content-Type' => 'application/json',
            );
        } else {
            $this->check_required_credentials();
            $ts = $this->seconds ();
            $nonce = Math.random ().toString (32).substr (2);
            $headers = array (
                'Content-Type' => 'application/json',
                '-x-ts' => $ts,
                '-x-nonce' => $nonce,
                '-x-key' => $this->apiKey,
            );
            $str_parms = '';
            $query = $this->keysort ($query);
            if ($method === 'POST') {
                $body = $this->json ($query);
                $str_parms = $body;
            }
            $sign_str = $str_parms . $ts . $nonce . $this->secret;
            $sign = $this->hash ($this->encode ($sign_str), 'sha256');
            $headers['-x-sign'] = $sign;
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
