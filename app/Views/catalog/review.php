<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>بازبینی کاتالوگ<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2>بازبینی کاتالوگ: <?= esc($catalog['title']) ?></h2>
            <div id="catalogStatus" class="badge bg-info">وضعیت: <?= $catalog['status'] ?></div>
        </div>
    </div>
</div>

<div class="row" id="pagesContainer">
    <?php foreach ($pages as $page): ?>
        <?php
            $proposal = json_decode($page['ai_proposal'], true);
            $layout = $proposal['layout'] ?? 'body';
            $isApproved = $page['status'] === 'approved';
        ?>
        <div class="col-md-6 mb-4" id="card-<?= $page['id'] ?>">
            <div class="card shadow-sm h-100 <?= $isApproved ? 'border-success' : '' ?>">
                <div class="card-header d-flex justify-content-between">
                    <span>صفحه <?= $page['page_number'] ?>: <?= esc($proposal['title'] ?? '') ?></span>
                    <?php if($isApproved): ?>
                        <span class="badge bg-success">تایید شده</span>
                    <?php endif; ?>
                </div>

                <!-- Mockup Preview Area -->
                <div class="card-body bg-light position-relative" style="height: 300px; overflow: hidden;">
                    <!-- Simple visual representation of layout -->
                    <div class="mockup-container d-flex flex-column h-100 bg-white border p-2">
                        <?php if ($layout === 'cover'): ?>
                            <div class="flex-grow-1 d-flex align-items-center justify-content-center bg-secondary text-white mb-2">
                                <?php if (!empty($proposal['suggested_images'][0]) && isset($imageMap[$proposal['suggested_images'][0]])): ?>
                                    <img src="<?= $imageMap[$proposal['suggested_images'][0]] ?>" style="max-width:100%; max-height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="bi bi-image fs-1"></i>
                                <?php endif; ?>
                            </div>
                            <div class="text-center">
                                <h5><?= esc($proposal['title'] ?? '') ?></h5>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-row h-100">
                                <div class="w-50 p-2">
                                    <h6><?= esc($proposal['title'] ?? '') ?></h6>
                                    <p class="small text-muted" style="font-size: 0.8rem;">
                                        <?= esc(mb_strimwidth($proposal['content'] ?? '', 0, 100, '...')) ?>
                                    </p>
                                </div>
                                <div class="w-50 bg-light d-flex align-items-center justify-content-center">
                                     <?php if (!empty($proposal['suggested_images'][0]) && isset($imageMap[$proposal['suggested_images'][0]])): ?>
                                        <img src="<?= $imageMap[$proposal['suggested_images'][0]] ?>" style="max-width:100%; max-height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <i class="bi bi-image fs-1"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">متن پیشنهادی:</h6>
                    <p class="card-text page-content" id="content-<?= $page['id'] ?>">
                        <?= nl2br(esc($proposal['content'] ?? '')) ?>
                    </p>
                </div>

                <div class="card-footer bg-white border-top-0 d-flex justify-content-end gap-2">
                    <?php if (!$isApproved): ?>
                        <button class="btn btn-outline-primary btn-sm btn-edit" data-page-id="<?= $page['id'] ?>" data-bs-toggle="modal" data-bs-target="#editModal">
                            <i class="bi bi-pencil"></i> اصلاح
                        </button>
                        <button class="btn btn-success btn-sm btn-approve" data-page-id="<?= $page['id'] ?>">
                            <i class="bi bi-check-lg"></i> تایید
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">اصلاح محتوا</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <input type="hidden" id="editPageId" name="page_id">
                    <div class="mb-3">
                        <label class="form-label">بازخورد یا متن جدید شما:</label>
                        <textarea class="form-control" id="userFeedback" name="user_feedback" rows="5" required></textarea>
                        <div class="form-text">مثال: "لطفاً عکس طبیعت بیشتر استفاده کن" یا "متن پاراگراف اول را به ... تغییر بده"</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">لغو</button>
                <button type="button" class="btn btn-primary" id="sendFeedbackBtn">
                    <span class="spinner-border spinner-border-sm d-none" id="feedbackSpinner"></span>
                    ارسال برای هوش مصنوعی
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Handle Edit Click
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('editPageId').value = this.getAttribute('data-page-id');
            document.getElementById('userFeedback').value = '';
        });
    });

    // Handle Feedback Submit
    document.getElementById('sendFeedbackBtn').addEventListener('click', function() {
        const pageId = document.getElementById('editPageId').value;
        const feedback = document.getElementById('userFeedback').value;
        const spinner = document.getElementById('feedbackSpinner');
        const btn = this;

        if (!feedback.trim()) return;

        btn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData();
        formData.append('page_id', pageId);
        formData.append('user_feedback', feedback);
        // CSRF
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('/catalog/feedback', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Update UI content
                const newContent = data.data.content;
                document.getElementById('content-' + pageId).innerHTML = newContent.replace(/\n/g, '<br>');
                // Ideally update preview image/title too if changed

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                alert('اصلاح انجام شد!');
            } else {
                alert('خطا: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Network Error'))
        .finally(() => {
            btn.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // Handle Approve Click
    document.querySelectorAll('.btn-approve').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('آیا از تایید این صفحه مطمئن هستید؟')) return;

            const pageId = this.getAttribute('data-page-id');
            const card = document.getElementById('card-' + pageId).querySelector('.card');

            const formData = new FormData();
            formData.append('page_id', pageId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('/catalog/approve', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    card.classList.add('border-success');
                    // Hide buttons
                    this.parentElement.innerHTML = '<span class="badge bg-success">تایید شده</span>';

                    if (data.all_approved) {
                        alert('تبریک! تمام صفحات تایید شدند. کاتالوگ آماده تولید نهایی است.');
                        document.getElementById('catalogStatus').textContent = 'وضعیت: تکمیل شده';
                        document.getElementById('catalogStatus').className = 'badge bg-success';
                    }
                }
            });
        });
    });

    // SSE for Status (Optional)
    const evtSource = new EventSource("/catalog/sse/<?= $catalog['id'] ?>");
    evtSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        // If status changes externally
        if (data.status === 'completed') {
             document.getElementById('catalogStatus').textContent = 'وضعیت: تکمیل شده';
             document.getElementById('catalogStatus').className = 'badge bg-success';
             evtSource.close();
        }
    };
</script>
<?= $this->endSection() ?>
