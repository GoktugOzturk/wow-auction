<?php



function getApiContent($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function checkForAuctionUpdate()
{
    $timestampFile = BASE_PATH . "data/auction_update_time";
    if (file_exists($timestampFile)) {
        $time = file_get_contents($timestampFile);
    } else {
        $time = 0;
    }
    if ($time > time() - 60) {
        return;
    }
    $data = json_decode(getApiContent("http://eu.battle.net/api/wow/auction/data/Tarren%20Mill"), true);
    foreach ($data['files'] as $file) {
        if ($file['lastModified'] / 1000 > $time) {
            $time = max($time, $file['lastModified'] / 1000);
            // echo date("r", $file['lastModified'] / 1000) . " - " . date("r");
            $auctions = getApiContent($file['url']);
            $auctions = preg_replace('@\[[^\]]+\]@i', '[]', $auctions);
            //echo strlen($auctions);
            file_put_contents(BASE_PATH . "data/auctions", $auctions);
            echo '<div class="alert alert-success" role="alert">Auction DB Updated!</div>';
        }
    }
    $time = max($time, time());
    file_put_contents($timestampFile, $time);
    //  echo "<xmp>" . print_r($data, 1) . "</xmp>";
}

function findItemAuctions($itemId)
{
    $auctionData = file_get_contents(BASE_PATH . "data/auctions");
    // {"auc":2125280144,"item":22791,"owner":"Ethansa","ownerRealm":"TarrenMill","bid":2867840,"buyout":3018780,"quantity":20,"timeLeft":"LONG","rand":0,"seed":1012116736,"context":0},
    preg_match_all('@\{"auc":[0-9]+,"item":' . $itemId . ',[^\}]+\}@', $auctionData, $auction_match);
    $auctions = array();
    if (count($auction_match)) {
        $auction_match = json_decode("[" . implode(",", $auction_match[0]) . "]", true);
        foreach ($auction_match as $auction) {
            if (isset($auctions[$auction['buyout']])) {
                $auctions[$auction['buyout']] += $auction['quantity'];
            } else {
                $auctions[$auction['buyout']] = $auction['quantity'];
            }
        }
    }
    ksort($auctions);
    //   echo "<xmp>" . print_r($auctions, 1) . "</xmp>";
    return $auctions;
}

function getItemData($itemId)
{
    $data = json_decode(getApiContent("http://eu.battle.net/api/wow/item/" . $itemId), true);
    //  echo "<xmp>" . print_r($data, 1) . "</xmp>";
    return $data;
}

function formatPrice($price)
{
    if ($price >= 10000) {
        return preg_replace("@([0-9]+)([0-9]{2})([0-9]{2})@", "$1g $2s $3b", $price);
    }
    if ($price >= 100) {
        return preg_replace("@([0-9]+)([0-9]{2})@", "$1s $2b", $price);
    } else {
        return $price . "b";
    }
}
