<?php
$apiUrl = 'https://www.cheapshark.com/api/1.0/';
$sortBy = 'DealRating';

function fetchData($endpoint, $queryParams, $sortBy) {
    global $apiUrl;
    $queryParams['sortBy'] = $sortBy;
    $queryString = http_build_query($queryParams);
    $requestUrl = "{$apiUrl}{$endpoint}?{$queryString}";
    $response = file_get_contents($requestUrl);
    $data = json_decode($response, true);

    if ($data !== null) {
        $result = [];
        foreach ($data as $key) {
            $result[] = formatElement($key, $endpoint);
        }
        return $result;
    } else {
        return ["error" => "Erreur lors de la récupération des données de l'API."];
    }
}

function formatElement($key, $endpoint) {
    $formattedElement = [
        "Thumb" => $key['thumb'],
    ];
    if ($endpoint === 'games') {
        // var_dump($key);
        if (isset($key['id'])) {
            $formattedElement = formatGameById($key);
        } else {
            $formattedElement = formatGameByTitle($key);
        }
    } elseif ($endpoint === 'deals') {
        $formattedElement["Title"] = $key['title'];
        $formattedElement["SalePrice"] = $key['salePrice'];
        $formattedElement["NormalPrice"] = $key['normalPrice'];
        $formattedElement["IsOnSale"] = ($key['isOnSale'] === '1' ? 'Yes' : 'No');
        $formattedElement["Savings"] = $key['savings'];
        $formattedElement["MetacriticScore"] = $key['metacriticScore'];
        $formattedElement["SteamRating"] = $key['steamRatingText']. " (" .$key['steamRatingPercent']. "% - " .$key['steamRatingCount']. " votes)";
        $formattedElement["SteamAppID"] = $key['steamAppID'];
        $formattedElement["ReleaseDate"] = date('Y-m-d', $key['releaseDate']);
        $formattedElement["DealRating"] = $key['dealRating'];
    }
    return $formattedElement;
}

function formatGameById($key) {
    $formattedElement = [
        "info" => [
            "title" => isset($key['title']) ? $key['title'] : null,
            "steamAppID" => isset($key['steamAppID']) ? $key['steamAppID'] : null,
            "thumb" => isset($key['thumb']) ? $key['thumb'] : null,
        ],
        "cheapestPriceEver" => [
            "price" => isset($key['cheapest']) ? $key['cheapest'] : null,
            "date" => isset($key['releaseDate']) ? $key['releaseDate'] : null,
        ],
        "deals" => [],
    ];
    if (isset($key['deals']) && is_array($key['deals'])) {
        foreach ($key['deals'] as $deal) {
            $formattedDeal = [
                "storeID" => isset($deal['storeID']) ? $deal['storeID'] : null,
                "dealID" => isset($deal['dealID']) ? $deal['dealID'] : null,
                "price" => isset($deal['price']) ? $deal['price'] : null,
                "retailPrice" => isset($deal['retailPrice']) ? $deal['retailPrice'] : null,
                "savings" => isset($deal['savings']) ? $deal['savings'] : null,
            ];
            $formattedElement["deals"][] = $formattedDeal;
        }
    }
    return $formattedElement;
}

function formatGameByTitle($key) {
    $formattedElement = [
        "gameID" => $key['gameID'],
        "steamAppID" => $key['steamAppID'],
        "cheapest" => $key['cheapest'],
        "cheapestDealID" => $key['cheapestDealID'],
        "external" => $key['external'],
        "internalName" => $key['internalName'],
        "thumb" => $key['thumb'],
    ];
    return $formattedElement;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'deals';
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($page) {
    case 'deals':
        $result = fetchData('deals', [], $sortBy);
        break;
    case 'games':
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $title = isset($_GET['title']) ? $_GET['title'] : null;
        $queryParams = [];
        if ($id) {
            $queryParams['gameID'] = $id;
            $result = fetchData('games', $queryParams, $sortBy);
        }
        if ($title) {
            $queryParams['title'] = $title;
            $result = fetchData('games', $queryParams, $sortBy);
        } else {
            $result = ["error" => "Title or ID is missing for the games page."];
        }
        break;
    default:
        $result = ["error" => "Page not found."];
        break;
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
?>
