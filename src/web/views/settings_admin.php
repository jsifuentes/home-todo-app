<?php
require_once __DIR__ . '../../../init.php';
?>

<div class="flex flex-col justify-between space-x-4 mt-8">
	<h2 class="text-xl font-bold mb-2">Settings</h2>

	<div id="setting-edit-form-result"></div>

	<div class="bg-white rounded-lg shadow mb-4">
		<table class="min-w-full">
			<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
				</tr>
			</thead>
			<tbody class="divide-y divide-gray-200">
				<?php foreach ($settings as $key => $value): ?>
					<tr x-data="{ editing: false }">
						<td class="px-6 py-4"><?= htmlspecialchars($settingsConfig[$key]['label']) ?></td>
						<td class="px-6 py-4">
							<div x-show="!editing">
								<span><?= htmlspecialchars($value) ?></span>
							</div>
							<div x-show="editing">
								<form class="flex" hx-post="/api/update_setting.php" hx-swap="innerHTML" hx-target="#setting-edit-form-result">
									<input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
									<?php if (isset($settingsConfig[$key]['options'])): ?>
										<select name="value" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
											<?php foreach ($settingsConfig[$key]['options']() as $option): ?>
												<option value="<?= htmlspecialchars($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= htmlspecialchars($option) ?></option>
											<?php endforeach; ?>
										</select>
									<?php else: ?>
										<input type="text" name="value" value="<?= htmlspecialchars($value) ?>" class="w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
									<?php endif; ?>
									<button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Save</button>
								</form>
							</div>
						</td>
						<td class="px-6 py-4">
							<button type="button" x-on:click="editing = !editing" class="text-blue-500 mr-2">Edit</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>