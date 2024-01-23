<?php
class ApiModel {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    public function fetchData($endpoint, $queryParams) {
        $queryString = http_build_query($queryParams);
        $requestUrl = "{$this->apiUrl}{$endpoint}?{$queryString}";
        $response = file_get_contents($requestUrl);
        $data = json_decode($response, true);

        if ($data !== null) {
            $result = [];
            foreach ($data as $key) {
                $result[] = $this->formatElement($key, $endpoint);
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
}
