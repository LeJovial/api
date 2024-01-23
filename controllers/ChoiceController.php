<?php
class ChoiceController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function processRequest($page, $id, $title) {
        if (!$page && !$id && !$title) {
            header('Content-Type: application/json');
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['status' => 404, 'message' => 'Page not found']);
            return ['status' => 404, 'message' => 'Page not found'];
        }

        switch ($page) {
            case 'deals':
                $result = $this->model->fetchData('deals', []);
                break;
            case 'games':
                $queryParams = [];
                if ($id) {
                    $queryParams['gameID'] = $id;
                }
                if ($title) {
                    $queryParams['title'] = $title;
                }
                if ($id || $title) {
                    $result = $this->model->fetchData('games', $queryParams);
                    $this->saveToDatabase($page, $id, $title);
                } else {
                    $result = ["error" => "Title or ID is missing for the games page."];
                }
                break;
            default:
                $result = ["error" => "Page not found."];
                break;
        }
        return $result;
    }

    private function saveToDatabase($page, $id, $title) {
        include 'bdd/bdd.php';
        $pageName = 'game';
        $stmtCheck = $dbh->prepare('SELECT * FROM memory WHERE page_name = :pageName AND page_id = :page_id AND title = :title');
        $stmtCheck->bindParam(':pageName', $pageName, PDO::PARAM_STR);
        $stmtCheck->bindParam(':page_id', $id, PDO::PARAM_STR);
        $stmtCheck->bindParam(':title', $title, PDO::PARAM_STR);
        $stmtCheck->execute();
        $existingData = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingData) {
            $apiData = $this->model->fetchData('games', ['gameID' => $id, 'title' => $title]);
            
            if (!empty($apiData)) {
                $apiData = $apiData[0];

                $stmtInsert = $dbh->prepare('INSERT INTO memory (page_name, page_id, title) VALUES (:pageName, :page_id, :title)');
                $stmtInsert->bindParam(':pageName', $pageName, PDO::PARAM_STR);
                $stmtInsert->bindParam(':page_id', $id, PDO::PARAM_STR);
                $stmtInsert->bindParam(':title', $title, PDO::PARAM_STR);
                $stmtInsert->execute();
            }
        }
        $stmtRetrieve = $dbh->prepare('SELECT * FROM memory WHERE page_name = :pageName AND page_id = :page_id AND title = :title');
        $stmtRetrieve->bindParam(':pageName', $pageName, PDO::PARAM_STR);
        $stmtRetrieve->bindParam(':page_id', $id, PDO::PARAM_STR);
        $stmtRetrieve->bindParam(':title', $title, PDO::PARAM_STR);
        $stmtRetrieve->execute();
        $result = $stmtRetrieve->fetchAll(PDO::FETCH_ASSOC);
    
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}

function formatGameById($gameData) {
    return $gameData;
}

function formatGameByTitle($gameData) {
    return $gameData;
}