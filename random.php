<?php
require_once 'includes/config.php';

requireAuth();

$pageTitle = t('random_title');
include 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4"><?php echo t('random_title'); ?></h2>
                    
                    <!-- Duration Filter -->
                    <div class="duration-presets mb-4">
                        <h6><?php echo t('random_duration_presets'); ?></h6>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary" data-duration="5">
                                5 <?php echo t('time_minutes'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-duration="15">
                                15 <?php echo t('time_minutes'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-duration="30">
                                30 <?php echo t('time_minutes'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-duration="60">
                                1 <?php echo t('time_hours'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Random Chore Display -->
                    <div id="random-chore-container" class="text-center">
                        <button id="get-random-chore" class="btn btn-primary btn-lg">
                            <?php echo t('random_get_chore'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedDuration = null;
    const durationButtons = document.querySelectorAll('.duration-presets button');
    const getRandomButton = document.getElementById('get-random-chore');
    const choreContainer = document.getElementById('random-chore-container');
    
    // Duration preset selection
    durationButtons.forEach(button => {
        button.addEventListener('click', function() {
            durationButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedDuration = parseInt(this.dataset.duration);
        });
    });
    
    // Get random chore
    getRandomButton.addEventListener('click', function() {
        fetch('includes/get_random_chore.php' + (selectedDuration ? `?duration=${selectedDuration}` : ''))
            .then(response => response.json())
            .then(data => {
                if (data.success && data.chore) {
                    choreContainer.innerHTML = `
                        <div class="chore-card mt-4">
                            <h4 class="mb-3">${data.chore.title}</h4>
                            ${data.chore.description ? `<p class="mb-3">${data.chore.description}</p>` : ''}
                            <div class="d-flex justify-content-between align-items-center text-muted">
                                <span>
                                    <i class="bi bi-clock"></i> ${data.chore.estimated_duration} <?php echo t('time_minutes'); ?>
                                </span>
                                <button class="btn btn-primary" onclick="startChore(${data.chore.id})">
                                    <?php echo t('btn_start'); ?>
                                </button>
                            </div>
                        </div>
                        <button id="get-random-chore" class="btn btn-outline-primary mt-3">
                            <?php echo t('random_get_chore'); ?>
                        </button>
                    `;
                } else {
                    choreContainer.innerHTML = `
                        <div class="alert alert-info mt-4">
                            <?php echo t('random_no_chores'); ?>
                            ${selectedDuration ? ` (≤ ${selectedDuration} <?php echo t('time_minutes'); ?>)` : ''}
                        </div>
                        <button id="get-random-chore" class="btn btn-primary mt-3">
                            <?php echo t('random_get_chore'); ?>
                        </button>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toast.show('An error occurred', 'error');
            });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
