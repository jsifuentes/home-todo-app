<?php

class Task
{
	public int $id;
	public string $title;
	public ?string $body;
	public int $sortOrder = 0;
	public string $dueDate;
	public string $dueDateIncrementSelected;
	public ?int $categoryId;
	public ?int $linkedToRecurringId;
	public string $createdAt;
	public string $updatedAt;

	public function __construct(array $data = [])
	{
		$this->id = $data['id'] ?? 0;
		$this->title = $data['title'] ?? '';
		$this->body = $data['body'] ?? null;
		$this->sortOrder = $data['sort_order'] ?? 0;
		$this->dueDate = $data['due_date'] ?? '';
		$this->dueDateIncrementSelected = $data['due_date_increment_selected'] ?? '';
		$this->categoryId = $data['category_id'] ?? null;
		$this->linkedToRecurringId = $data['linked_to_recurring_id'] ?? null;
		$this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
		$this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'body' => $this->body,
			'sort_order' => $this->sortOrder,
			'due_date' => $this->dueDate,
			'due_date_increment_selected' => $this->dueDateIncrementSelected,
			'category_id' => $this->categoryId,
			'linked_to_recurring_id' => $this->linkedToRecurringId,
			'created_at' => $this->createdAt,
			'updated_at' => $this->updatedAt
		];
	}
}

function createTask(SQLite3 $db, Task $task): int
{
	$stmt = $db->prepare("
        INSERT INTO tasks (
            title, body, sort_order, due_date, due_date_increment_selected, 
            category_id, linked_to_recurring_id
        ) VALUES (
            :title, :body, :sort_order, :due_date, :due_date_increment_selected,
            :category_id, :linked_to_recurring_id
        )
    ");

	$stmt->bindValue(':title', $task->title);
	$stmt->bindValue(':body', $task->body);
	$stmt->bindValue(':sort_order', $task->sortOrder);
	$stmt->bindValue(':due_date', $task->dueDate);
	$stmt->bindValue(':due_date_increment_selected', $task->dueDateIncrementSelected);
	$stmt->bindValue(':category_id', $task->categoryId);
	$stmt->bindValue(':linked_to_recurring_id', $task->linkedToRecurringId);

	$stmt->execute();

	$id = $db->lastInsertRowID();
	$task->id = $id;
	return $id;
}
