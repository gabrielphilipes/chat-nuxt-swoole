<?php

require __DIR__ . '/WebsocketTrait.php';

use Swoole\WebSocket\Server;

$connections = [];


class WebsocketSwoole
{
	use WebsocketTrait;

	/** @var Server  */
	private Server $server;

	public function __construct()
	{
		$this->server = new Server('0.0.0.0', 9502);
		$this->server->on('Start', [$this, 'onStart']);
		$this->server->on('Open', [$this, 'onOpen']);
		$this->server->on('Message', [$this, 'onMessage']);
		$this->server->on('Close', [$this, 'onClose']);

		// Swoole Table
		$this->swooleTable();
	}

	public function onStart(Server $server): void
	{
		echo 'Swoole WebSocket Server is started at http://' . PHP_EOL;
	}

	public function onOpen(Server $server, Swoole\Http\Request $request)
	{
		$userData = $this->validateToken($request->get['token'] ?? null);
		if (!$userData) {
			echo 'Token is required' . PHP_EOL;
			$server->close($request->fd);
			return;
		}

		$this->saveSwooleTable($request->fd, $userData->business_id, $userData->user_id);

		echo "connection open: {$request->fd}" . PHP_EOL;

		$server->tick(1000, function() use ($server, $request)
		{
			// $server->push($request->fd, json_encode(['ping' => time()]));
		});
	}

	public function onMessage(Server $server, Swoole\WebSocket\Frame $frame)
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

		if (empty($input->to_user)) {
			$server->push($frame->fd, json_encode(['error' => 'Invalid to_user']));
			return;
		}

		// $frame->fd = ID da conexão que enviou a mensagem
		$thisConnection = $this->getConnSwooleTable($frame->fd); // user_id | business_id

		$responseTo = $frame->fd;
		$sendConfirmRequestMessage = false;
		$confirmRequestMessage = ['confirm_message' => true];

		$response = [];
		switch ($input->type) {
			case 'message':
				$responseTo = [];
				foreach ($input->to_user as $userId) {
					// empresa = 2
					// usuário = 123 -> 6
					$connections = $this->getUserSwooleTable($thisConnection->business_id, $userId);
					// NULL
					// [2,12]
					if (empty($connections)) {
						continue;
					}

					$responseTo = array_merge($responseTo, $connections);
				}

				$responseTo = array_unique($responseTo); // Lista dos IDs das conexões do Gabriel

				$sendConfirmRequestMessage = true;
				$confirmRequestMessage = ['confirm_message' => true, 'to_user' => $input->to_user];

				$response = ['type' => 'received_message', 'message' => $input->data->content];
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

		if (is_array($responseTo)) {
			foreach ($responseTo as $fd) {
				$server->push($fd, json_encode($response));
			}
		} else {
			$server->push($responseTo, json_encode($response));
		}

		if ($sendConfirmRequestMessage) {
			$server->push($frame->fd, json_encode($confirmRequestMessage));
		}
	}

	public function onClose(Server $server, int $fd)
	{
		echo "connection close: {$fd}\n";

		$this->removeSwooleTable($fd);
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		$this->server->start();
	}
}

