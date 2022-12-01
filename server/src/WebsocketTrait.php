<?php

use Swoole\Table;

trait WebsocketTrait
{
	private Table $table;

	/**
	 * @param string|null $token
	 * @return int|null
	 */
	private function validateToken(?string $token = null): ?stdClass
	{
		if (!$token) {
			return null;
		}

		$token = base64_decode($token);
		$token = json_decode($token);
		if (json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		if (empty($token->user_id) || empty($token->business_id)) {
			return null;
		}

		return $token;
	}

	private function swooleTable()
	{
		$this->table = new Table(1024);
		$this->table->column('content', Table::TYPE_STRING, 1024);
		$this->table->create();
	}

	// Salvar a conexão no Swoole Table
	private function saveSwooleTable(int $fd, int $businessId, int $userId)
	{
		$keyConn = 'conn:' . $fd;
		$keyUser = 'user:' . $businessId . ':' . $userId;

		// Conn
		$this->table->set($keyConn, ['content' => json_encode([
			'business_id' => $businessId,
			'user_id' => $userId,
		])]);

		// User
		// Edit
		if ($conn = $this->table->get($keyUser)) {
			$listConnection = $conn['content'];
			$this->editSwooleTable(json_decode($listConnection), $fd, $keyUser);
			return;
		}

		// Salvar
		$contentUser = json_encode([$fd]);
		$this->table->set($keyUser, ['content' => $contentUser]);
	}

	// Edit
	private function editSwooleTable(array $connections, int $fd, string $keyUser): void
	{
		$connections[] = $fd;
		$contentUser = json_encode($connections);
		$this->table->set($keyUser, ['content' => $contentUser]);
	}

	// Buscar o registro, baseado no ID do usuário

	private function getUserSwooleTable(int $businessId, int $userId): ?array
	{
		if ($data = $this->table->get('user:' . $businessId . ':' . $userId)) {
			return json_decode($data['content']);
		}

		return null;
	}

	// Busca o registro, baseado no ID da conexão
	private function getConnSwooleTable(int $fd): ?stdClass
	{
		if ($data = $this->table->get('conn:' . $fd)) {
			return json_decode($data['content']);
		}

		return null;
	}

	// Remover o registro
	private function removeSwooleTable(int $fd): void
	{
		$userData = $this->getConnSwooleTable($fd); // business_id | user_id
		$userId = $userData->user_id;

		// Remover a conexão
		$this->table->del('conn:' . $fd);

		// Remover o usuário
		$userConnections = $this->getUserSwooleTable($userData->business_id, $userId); // Array
		if (count($userConnections) === 1) {
			$this->table->del('user:' . $userData->business_id . ':' . $userId);
			return;
		}

		// $userConnections = array_diff($userConnections, [$fd]);
		$userConnections = array_filter($userConnections, fn($item) => $item !== $fd);
		$this->table->set('user:' . $userData->business_id . ':' . $userId, ['content' => json_encode($userConnections)]);
	}
}
