<?php

class RecurringTask
{
	public int $id;
	public string $title;
	public ?string $body;
	public string $dueDateIncrementSelected;
	public ?int $categoryId;
	public bool $isActive;
	public int $recurrenceAmount;
	public string $recurrenceUnit;
	public ?int $recurrenceDayOfWeek;
	public ?int $recurrenceDayOfMonth;
	public ?int $recurrenceMonth;
	public string $lastGeneratedAt;
	public string $createdAt;
	public string $updatedAt;

	public function __construct(array $data = [])
	{
		$this->id = $data['id'] ?? 0;
		$this->title = $data['title'] ?? '';
		$this->body = $data['body'] ?? null;
		$this->dueDateIncrementSelected = $data['due_date_increment_selected'] ?? '';
		$this->categoryId = $data['category_id'] ?? null;
		$this->isActive = (bool)($data['is_active'] ?? true);
		$this->recurrenceAmount = $data['recurrence_amount'] ?? 0;
		$this->recurrenceUnit = $data['recurrence_unit'] ?? '';
		$this->recurrenceDayOfWeek = $data['recurrence_day_of_week'] ?? null;
		$this->recurrenceDayOfMonth = $data['recurrence_day_of_month'] ?? null;
		$this->recurrenceMonth = $data['recurrence_month'] ?? null;
		$this->lastGeneratedAt = $data['last_generated_at'] ?? date('Y-m-d H:i:s');
		$this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
		$this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'title' => $this->title,
			'body' => $this->body,
			'due_date_increment_selected' => $this->dueDateIncrementSelected,
			'category_id' => $this->categoryId,
			'is_active' => $this->isActive,
			'recurrence_amount' => $this->recurrenceAmount,
			'recurrence_unit' => $this->recurrenceUnit,
			'recurrence_day_of_week' => $this->recurrenceDayOfWeek,
			'recurrence_day_of_month' => $this->recurrenceDayOfMonth,
			'recurrence_month' => $this->recurrenceMonth,
			'last_generated_at' => $this->lastGeneratedAt,
			'created_at' => $this->createdAt,
			'updated_at' => $this->updatedAt
		];
	}

	public function getNextScheduledGeneration(): DateTime
	{
		// Calculate next scheduled generation based on recurrence pattern
		$nextScheduled = new DateTime($this->lastGeneratedAt);

		switch ($this->recurrenceUnit) {
			case 'd':
				$nextScheduled->modify("+{$this->recurrenceAmount} days");
				break;

			case 'w':
				// For weekly tasks, move to next occurrence of the specified weekday
				$currentDayOfWeek = (int)$nextScheduled->format('N');
				$targetDayOfWeek = $this->recurrenceDayOfWeek;
				$daysToAdd = ($targetDayOfWeek - $currentDayOfWeek + 7) % 7;
				if ($daysToAdd === 0) {
					$daysToAdd = 7;
				}
				$nextScheduled->modify("+{$daysToAdd} days");

				// Add additional weeks if recurrence amount > 1
				if ($this->recurrenceAmount > 1) {
					$nextScheduled->modify("+" . ($this->recurrenceAmount - 1) . " weeks");
				}
				break;

			case 'm':
				$nextScheduled->setDate(
					(int)$nextScheduled->format('Y'),
					(int)$nextScheduled->format('m'),
					$this->recurrenceDayOfMonth
				);
				$nextScheduled->modify("+{$this->recurrenceAmount} months");
				break;

			case 'y':
				$nextScheduled->setDate(
					(int)$nextScheduled->format('Y'),
					$this->recurrenceMonth,
					$this->recurrenceDayOfMonth
				);
				$nextScheduled->modify("+{$this->recurrenceAmount} years");
				break;
		}

		return $nextScheduled;
	}
}

function getRecurringTaskById(SQLite3 $db, int $id): ?RecurringTask
{
	$stmt = $db->prepare("SELECT * FROM recurring_tasks WHERE id = :id");
	$stmt->bindValue(':id', $id);
	$result = $stmt->execute();
	$row = $result->fetchArray(SQLITE3_ASSOC);
	if (!$row) {
		return null;
	}

	return new RecurringTask($row);
}

function createRecurringTask(SQLite3 $db, RecurringTask $definition): int
{
	$stmt = $db->prepare("
        INSERT INTO recurring_tasks (
            title, body, due_date_increment_selected, category_id,
            recurrence_amount, recurrence_unit,
            recurrence_day_of_week, recurrence_day_of_month, recurrence_month
        ) VALUES (
            :title, :body, :due_date_increment_selected, :category_id,
            :recurrence_amount, :recurrence_unit,
            :recurrence_day_of_week, :recurrence_day_of_month, :recurrence_month
        )
    ");

	$stmt->bindValue(':title', $definition->title);
	$stmt->bindValue(':body', $definition->body);
	$stmt->bindValue(':due_date_increment_selected', $definition->dueDateIncrementSelected);
	$stmt->bindValue(':category_id', $definition->categoryId);
	$stmt->bindValue(':recurrence_amount', $definition->recurrenceAmount);
	$stmt->bindValue(':recurrence_unit', $definition->recurrenceUnit);
	$stmt->bindValue(':recurrence_day_of_week', $definition->recurrenceUnit === 'w' ? $definition->recurrenceDayOfWeek : null);
	$stmt->bindValue(':recurrence_day_of_month', ($definition->recurrenceUnit === 'm' || $definition->recurrenceUnit === 'y') ? $definition->recurrenceDayOfMonth : null);
	$stmt->bindValue(':recurrence_month', $definition->recurrenceUnit === 'y' ? $definition->recurrenceMonth : null);

	$stmt->execute();
	$id = $db->lastInsertRowID();

	$definition->id = $id;
	return $id;
}

function createTaskFromRecurringTask(SQLite3 $db, RecurringTask $recurringTask): int
{
	// Create a new task based on the recurring task definition
	$task = new Task();
	$task->title = $recurringTask->title;
	$task->body = $recurringTask->body;
	$task->dueDateIncrementSelected = $recurringTask->dueDateIncrementSelected;
	$task->categoryId = $recurringTask->categoryId;
	$task->linkedToRecurringId = $recurringTask->id;

	// Calculate the due date based on the recurring task due date increment
	$dueDate = calculateEndDate($recurringTask->dueDateIncrementSelected);
	$task->dueDate = $dueDate->format('Y-m-d H:i:s');

	// Create the task and update the last generated timestamp
	$taskId = createTask($db, $task);

	$stmt = $db->prepare("UPDATE recurring_tasks SET last_generated_at = CURRENT_TIMESTAMP WHERE id = :id");
	$stmt->bindValue(':id', $recurringTask->id);
	$stmt->execute();

	return $taskId;
}
