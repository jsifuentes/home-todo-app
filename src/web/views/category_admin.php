<?php
require_once __DIR__ . '../../../init.php';

// Get categories
$categories = $db->query("SELECT * FROM categories ORDER BY is_default DESC, name ASC");
?>

<div class="flex flex-col justify-between space-x-4">
	<h2 class="text-xl font-bold mb-2">Categories</h2>

	<div id="category-edit-form-result"></div>

	<div class="bg-white rounded-lg shadow mb-4">
		<table class="min-w-full">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-gray-200">
				<?php while ($category = $categories->fetchArray(SQLITE3_ASSOC)): ?>
					<tr x-data="{ editing: false }">
						<td class="px-6 py-4">
							<div x-show="!editing">
								<span @click="editing = true" class="cursor-pointer hover:text-blue-500"><?= htmlspecialchars($category['name']) ?></span>
							</div>
							<div x-show="editing">
								<form class="edit-category-form flex" hx-post="/api/update_category.php" hx-swap="innerHTML" hx-target="#category-edit-form-result">
									<input type="hidden" name="category_id" value="<?= $category['id'] ?>">
									<input type="text" name="name"
										value="<?= htmlspecialchars($category['name']) ?>"
										class="w-full px-2 py-1 border rounded">
									<button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
								</form>
							</div>
						</td>
						<td class="px-6 py-4">
							<?php if ($category['is_default']): ?>
								<span class="text-green-500">✓</span>
							<?php else: ?>
								<button
									hx-post="/api/update_category.php"
									hx-vals='{"category_id": "<?= $category['id'] ?>", "is_default": 1}'
									class="text-blue-500 hover:text-blue-900">
									Set as default
								</button>
							<?php endif; ?>
						</td>
						<td class="px-6 py-4"><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
						<td class="px-6 py-4">
							<button @click="editing = true" x-show="!editing" class="text-blue-500 hover:text-blue-900">Edit</button>
							<button @click="editing = false" x-show="editing" class="text-red-500 hover:text-red-900">Cancel</button>
							<?php if (!$category['is_default']): ?>
								<button
									hx-delete="/api/delete_category.php?categoryId=<?= $category['id'] ?>"
									hx-confirm="Are you sure you want to delete this category?"
									class="text-red-600 hover:text-red-900">
									Delete
								</button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

	<div class="bg-white rounded-lg shadow p-4">
		<h3 class="text-lg font-semibold mb-4">Create New Category</h3>
		<form hx-post="/api/create_category.php" hx-swap="innerHTML" hx-target="#category-form-result">
			<div id="category-form-result"></div>

			<div class="mb-4">
				<label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
				<input type="text" id="name" name="name" required
					class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
			</div>

			<div class="mb-4">
				<label class="flex items-center">
					<input type="checkbox" name="is_default" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
					<span class="ml-2 text-sm text-gray-600">Set as default category</span>
				</label>
			</div>

			<button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
				Create Category
			</button>
		</form>
	</div>
</div>