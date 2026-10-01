<?php
$title = 'Service Categories';
require __DIR__ . '/../includes/header.php';
require_login('admin');
require __DIR__ . '/../config/database.php';

$message = '';
$error = '';
$editCategory = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf'] ?? '');
    if (!verify_csrf($token)) {
        $error = 'Your session token is invalid or expired. Please refresh the page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        try {
            if ($action === 'create') {
                $name = trim((string)($_POST['name'] ?? ''));
                $fee = (string)($_POST['access_fee'] ?? '0');
                $description = trim((string)($_POST['description'] ?? ''));
                $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

                if ($name === '') {
                    throw new RuntimeException('Service category name is required.');
                }
                if (!is_numeric($fee) || (float)$fee < 0) {
                    throw new RuntimeException('Access fee must be a valid amount of Rs. 0 or more.');
                }

                $stmt = $pdo->prepare('INSERT INTO service_categories (name, access_fee, description, status) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, number_format((float)$fee, 2, '.', ''), $description !== '' ? $description : null, $status]);
                $message = 'Service category added successfully.';
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim((string)($_POST['name'] ?? ''));
                $fee = (string)($_POST['access_fee'] ?? '0');
                $description = trim((string)($_POST['description'] ?? ''));
                $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

                if ($id < 1) {
                    throw new RuntimeException('Invalid service category.');
                }
                if ($name === '') {
                    throw new RuntimeException('Service category name is required.');
                }
                if (!is_numeric($fee) || (float)$fee < 0) {
                    throw new RuntimeException('Access fee must be a valid amount of Rs. 0 or more.');
                }

                $stmt = $pdo->prepare('UPDATE service_categories SET name=?, access_fee=?, description=?, status=? WHERE id=?');
                $stmt->execute([$name, number_format((float)$fee, 2, '.', ''), $description !== '' ? $description : null, $status, $id]);
                $message = 'Service category updated successfully.';
            } elseif ($action === 'toggle') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id < 1) {
                    throw new RuntimeException('Invalid service category.');
                }

                $stmt = $pdo->prepare("UPDATE service_categories SET status = IF(status='active','inactive','active') WHERE id=?");
                $stmt->execute([$id]);
                $message = 'Service category status updated.';
            }
        } catch (PDOException $ex) {
            if ((string)$ex->errorInfo[1] === '1062') {
                $error = 'A service category with that name already exists.';
            } else {
                $error = 'The service category could not be saved. Please check the database and try again.';
            }
        } catch (Throwable $ex) {
            $error = $ex->getMessage();
        }
    }
}

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    if ($editId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE id=? LIMIT 1');
        $stmt->execute([$editId]);
        $editCategory = $stmt->fetch() ?: null;
        if (!$editCategory) {
            $error = 'The selected service category was not found.';
        }
    }
}

$rows = $pdo->query(
    "SELECT sc.*, COUNT(DISTINCT ps.id) AS provider_service_count, COUNT(DISTINCT ssp.id) AS subproblem_count
     FROM service_categories sc
     LEFT JOIN provider_services ps ON ps.category_id = sc.id
     LEFT JOIN service_subproblems ssp ON ssp.category_id = sc.id
     GROUP BY sc.id
     ORDER BY sc.id"
)->fetchAll();
?>

<section class="section-pad">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <h1 class="fw-bold mb-1">Service Categories & Fees</h1>
        <p class="text-muted mb-0">Add, edit, activate, or deactivate the roadside assistance services offered by INFINEX.</p>
      </div>
      <?php if ($editCategory): ?>
        <a class="btn btn-outline-dark" href="services.php"><i class="bi bi-plus-lg me-1"></i> Add New Category</a>
      <?php endif; ?>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i><?=e($message)?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i><?=e($error)?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h4 class="fw-bold mb-3"><?= $editCategory ? 'Edit Service Category' : 'Add Service Category' ?></h4>
          <form method="post" action="services.php<?= $editCategory ? '?edit='.(int)$editCategory['id'] : '' ?>">
            <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
            <input type="hidden" name="action" value="<?=$editCategory ? 'update' : 'create'?>">
            <?php if ($editCategory): ?><input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>"><?php endif; ?>

            <div class="mb-3">
              <label class="form-label fw-semibold">Category name <span class="text-danger">*</span></label>
              <input class="form-control form-control-lg" type="text" name="name" maxlength="100" required value="<?=e($editCategory['name'] ?? '')?>" placeholder="e.g. Fuel / Fuel Delivery">
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Access fee (Rs.) <span class="text-danger">*</span></label>
              <input class="form-control form-control-lg" type="number" name="access_fee" min="0" step="0.01" required value="<?=e(isset($editCategory['access_fee']) ? (string)$editCategory['access_fee'] : '0.00')?>" placeholder="500.00">
              <div class="form-text">This is the fee charged to the customer before provider details are released.</div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Description</label>
              <textarea class="form-control" name="description" rows="4" maxlength="2000" placeholder="Briefly describe what this service covers."><?=e($editCategory['description'] ?? '')?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select form-select-lg" name="status">
                <option value="active" <?= (($editCategory['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (($editCategory['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">
              <i class="bi <?= $editCategory ? 'bi-save' : 'bi-plus-lg' ?> me-1"></i>
              <?= $editCategory ? 'Save Changes' : 'Add Category' ?>
            </button>
            <?php if ($editCategory): ?>
              <a class="btn btn-outline-secondary w-100 mt-2" href="services.php">Cancel</a>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-3">
          <div class="d-flex justify-content-between align-items-center px-2 pt-2 pb-3">
            <div>
              <h4 class="fw-bold mb-1">Current Categories</h4>
              <small class="text-muted"><?=count($rows)?> service categories configured</small>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Category</th>
                  <th>Access Fee</th>
                  <th>Providers</th>
                  <th>Problems</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$rows): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No service categories have been added yet.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?=e($r['name'])?></div>
                      <?php if (!empty($r['description'])): ?><small class="text-muted"><?=e($r['description'])?></small><?php endif; ?>
                    </td>
                    <td class="fw-semibold">Rs. <?=number_format((float)$r['access_fee'], 2)?></td>
                    <td><?= (int)$r['provider_service_count'] ?></td>
                    <td><?= (int)$r['subproblem_count'] ?></td>
                    <td>
                      <span class="badge rounded-pill <?= $r['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?=e(ucfirst($r['status']))?></span>
                    </td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary" href="services.php?edit=<?= (int)$r['id'] ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                      <form method="post" action="services.php" class="d-inline" onsubmit="return confirm('Change the status of this service category?');">
                        <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm <?= $r['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                          <?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="alert alert-info mt-3 mb-0">
          <i class="bi bi-info-circle me-1"></i>
          Deactivating a category keeps its existing records and provider relationships, but prevents it from being offered as a new customer service while inactive.
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
