<?php

use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;


$server = new Server("0.0.0.0", 9502);

$server->on('Start', function(Server $server)
{
	echo "Swoole WebSocket Server is started at http://127.0.0.1:9502\n";
});

$server->on('Open', function(Server $server, Swoole\Http\Request $request)
{
	echo "connection open: {$request->fd}\n";

	// var_dump($request);
	// var_dump($server);

	// setInterval - javascript

	// ping-pong
	// ping-pong :: servidor envia um PING e ele espera um PONG, em até 2mins.
	// ping-pong :: nÃo receber um PONG em até 2mins, o servidor fecha a conexÃ£o.
	$server->tick(1000, function() use ($server, $request)
	{
		// $server->push($request->fd, json_encode(['ping' => time()]));
	});
});

$server->on('Message', function(Server $server, Frame $frame)
{
	$input = json_decode($frame->data);
	if (json_last_error() !== JSON_ERROR_NONE) {
		$server->push($frame->fd, json_encode(['error' => 'Invalid JSON']));
		return;
	}

	// message
	// status
	// alert
	$typesAccepts = ['message', 'status', 'alert'];
	if (empty($input->type) || !in_array($input->type, $typesAccepts)) {
		$server->push($frame->fd, json_encode(['error' => 'Invalid type']));
		return;
	}

//	$response = DispacthActions::execute($input);
//
//	if ($response->error) {
//		$server->push($frame->fd, json_encode(['error' => $response->message]));
//		return;
//	}
//
//	$server->push($frame->fd, json_encode($response->data));

	$responseTo = $frame->fd;
	$response = [];
	switch ($input->type) {
		case 'message':
			// return Message::receiveMessage($input->data);
			// Salvar a mensagem no banco
			// Verificar se a mensagem é um comando, de ação automática -> executar a ação automática
			// Buscar ID da conexão do usuário de destino
			// ID === 1
			if ($responseTo % 2) {
				$responseTo = $responseTo + 1;
				// 1
			} else {
				$responseTo = $responseTo - 1;
				// 2
			}

			// Envio a mensagem para o usuário :: $server->push(alessandro, json_encode(['message' => 'Olá, tudo bem?']));

			$response = ['confirm_message' => true];
			break;

		case 'status':
			// Listar todos os usuários da empresa, via banco de dados
			// Verificar se cada usuário está online ou offline
			// Montar um array de resposta

			$response = ['status' => [
				'user_id' => 123,
				'username' => 'mamut',
			]];
			break;

		case 'alert':
			$response = ['alert' => [
				'user_id' => 123,
				'message' => 'Hello World',
			]];
			break;
	}

	echo "received message: {$frame->data}\n";

	if (!$server->exists($responseTo)) {
		$server->push($frame->fd, json_encode(['error' => 'connection is closed']));
		return;
	}

	foreach ([1,2,3] as $item) {
		$server->push($item, json_encode($response));
	}
});

$server->on('Close', function(Server $server, int $fd)
{
	echo "connection close: {$fd}\n";
//	$server->disconnect($fd);
});

$server->on('Disconnect', function(Server $server, int $fd)
{
	echo "connection disconnect: {$fd}\n";
});

$server->start();




// BAN

























