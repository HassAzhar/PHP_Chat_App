<?php
namespace App;

use FFI\Exception;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

include './SqlLiteConnection.php';

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/messages', function (Request $request, Response $response, $args) {
    try{
        $userId = $request->getQueryParams()['userId'];
        $db = (new SQLiteConnection())->connect();
        $query = $db->prepare('SELECT from_user_id, timestamp, message FROM main.messages WHERE to_user_id=:userId and transferred=0;');
        $query->bindValue(':userId', $userId);
        $query->execute();
        $query_response = $query->fetchAll(\PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($query_response));

        $query = $db->prepare('UPDATE main.messages SET transferred=1 WHERE to_user_id=:userId and transferred=0;');
        $query->bindValue(':userId', $userId);
        $query->execute();
        return $response;
    }
    catch (Exception $error){
        return json_encode(array('message' => 'Error in Getting', 'status' => 400, 'debug' => $error));
    }
});

$app->post('/messages', function (Request $request, Response $response, $args) {
    try{
        $fromUserId = $request->getQueryParams()['fromUserId'];
        $toUserId = $request->getQueryParams()['toUserId'];
        $message = $request->getQueryParams()['message'];
        $db = (new SQLiteConnection())->connect();

        $query = $db->prepare('INSERT INTO main.messages ( to_user_id, from_user_id, message, timestamp, transferred)
                        VALUES (:toUserId, :fromUserId, :message, (SELECT strftime(\'%s\', \'now\')), 0);');
        $query->bindValue(':toUserId', $toUserId);
        $query->bindValue(':fromUserId', $fromUserId);
        $query->bindValue(':message', $message);
        $result = $query->execute();
        if (!$result)
            throw new Exception();

        $response->getBody()->write(json_encode(array('message' => 'Message sent', 'status' => 200)));
        return $response;
    }
    catch (Exception $error){
        return json_encode(array('message' => 'Error in Sending', 'status' => 400, 'debug' => $error));
    }
});

$app->run();