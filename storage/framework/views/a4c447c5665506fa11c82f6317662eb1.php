<?php $__env->startSection('title', __('email_providers.page_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?php echo e($emailDocContent['title'] ?? __('email_providers.title')); ?></h1>
                <div class="language-selector">
                    <select class="form-select" onchange="window.location.href = '<?php echo e(route('admin.set.language')); ?>?lang=' + this.value">
                        <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php echo e($currentLanguage === $code ? 'selected' : ''); ?>>
                                <?php echo e($name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="markdown-content">
                        <?php if(isset($emailDocContent) && !empty($emailDocContent)): ?>
                            <div id="markdown-content">
                                <h1><?php echo e($emailDocContent['title'] ?? __('email_providers.title')); ?></h1>
                                <p><?php echo e($emailDocContent['description']); ?></p>
                                
                                <h2><?php echo e($emailDocContent['table_of_contents']); ?></h2>
                                <ol>
                                    <li><a href="#introduction"><?php echo e($emailDocContent['introduction']['title']); ?></a></li>
                                    <li><a href="#smtp"><?php echo e($emailDocContent['smtp']['title']); ?></a></li>
                                    <li><a href="#phpmail"><?php echo e($emailDocContent['phpmail']['title']); ?></a></li>
                                    <li><a href="#mailgun"><?php echo e($emailDocContent['mailgun']['title']); ?></a></li>
                                    <li><a href="#mailchimp"><?php echo e($emailDocContent['mailchimp']['title']); ?></a></li>
                                    <li><a href="#rapidmail"><?php echo e($emailDocContent['rapidmail']['title']); ?></a></li>
                                    <li><a href="#comparison"><?php echo e($emailDocContent['comparison']['title']); ?></a></li>
                                    <li><a href="#troubleshooting"><?php echo e($emailDocContent['troubleshooting']['title']); ?></a></li>
                                </ol>
                                
                                <h2 id="introduction"><?php echo e($emailDocContent['introduction']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['introduction']['description']); ?></p>
                                
                                <h2 id="smtp"><?php echo e($emailDocContent['smtp']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['smtp']['description']); ?></p>
                                
                                <h3><?php echo e($emailDocContent['smtp']['configuration']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['smtp']['config_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['smtp']['advantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['smtp']['advantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($advantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['smtp']['disadvantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['smtp']['disadvantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($disadvantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['smtp']['example']); ?></h3>
                                
                                <h2 id="phpmail"><?php echo e($emailDocContent['phpmail']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['phpmail']['description']); ?></p>
                                
                                <h3><?php echo e($emailDocContent['phpmail']['configuration']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['phpmail']['config_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['phpmail']['advantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['phpmail']['advantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($advantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['phpmail']['disadvantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['phpmail']['disadvantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($disadvantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['phpmail']['example']); ?></h3>
                                
                                <h2 id="mailgun"><?php echo e($emailDocContent['mailgun']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['mailgun']['description']); ?></p>
                                
                                <h3><?php echo e($emailDocContent['mailgun']['configuration']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailgun']['config_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailgun']['advantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailgun']['advantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($advantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailgun']['disadvantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailgun']['disadvantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($disadvantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailgun']['example']); ?></h3>
                                
                                <h2 id="mailchimp"><?php echo e($emailDocContent['mailchimp']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['mailchimp']['description']); ?></p>
                                
                                <h3><?php echo e($emailDocContent['mailchimp']['configuration']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailchimp']['config_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailchimp']['advantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailchimp']['advantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($advantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailchimp']['disadvantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['mailchimp']['disadvantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($disadvantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['mailchimp']['example']); ?></h3>
                                
                                <h2 id="rapidmail"><?php echo e($emailDocContent['rapidmail']['title']); ?></h2>
                                <p><?php echo e($emailDocContent['rapidmail']['description']); ?></p>
                                
                                <h3><?php echo e($emailDocContent['rapidmail']['configuration']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['rapidmail']['config_items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($item); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['rapidmail']['advantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['rapidmail']['advantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $advantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($advantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['rapidmail']['disadvantages']); ?></h3>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['rapidmail']['disadvantages_list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $disadvantage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($disadvantage); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h3><?php echo e($emailDocContent['rapidmail']['example']); ?></h3>
                                
                                <h2 id="comparison"><?php echo e($emailDocContent['comparison']['title']); ?></h2>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th><?php echo e($emailDocContent['comparison']['title']); ?></th>
                                            <th><?php echo e($emailDocContent['comparison']['deliverability']); ?></th>
                                            <th><?php echo e($emailDocContent['comparison']['price']); ?></th>
                                            <th><?php echo e($emailDocContent['comparison']['ease_of_setup']); ?></th>
                                            <th><?php echo e($emailDocContent['comparison']['advanced_features']); ?></th>
                                            <th><?php echo e($emailDocContent['comparison']['gdpr_compliance']); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>SMTP</td>
                                            <td><?php echo e($emailDocContent['comparison']['variable']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['free']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['moderate']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['limited']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['depends']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>PHPMail</td>
                                            <td><?php echo e($emailDocContent['comparison']['variable']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['free']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['moderate']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['moderate']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['depends']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Mailgun</td>
                                            <td><?php echo e($emailDocContent['comparison']['high']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['freemium']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['easy']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['numerous']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['good']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Mailchimp</td>
                                            <td><?php echo e($emailDocContent['comparison']['very_high']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['paid']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['moderate']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['very_numerous']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['good']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Rapidmail</td>
                                            <td><?php echo e($emailDocContent['comparison']['high']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['paid']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['easy']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['numerous']); ?></td>
                                            <td><?php echo e($emailDocContent['comparison']['excellent']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <h2 id="troubleshooting"><?php echo e($emailDocContent['troubleshooting']['title']); ?></h2>
                                <h3><?php echo e($emailDocContent['troubleshooting']['common_problems']); ?></h3>
                                
                                <h4><?php echo e($emailDocContent['troubleshooting']['emails_not_sent']); ?></h4>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['troubleshooting']['emails_not_sent_tips']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($tip); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h4><?php echo e($emailDocContent['troubleshooting']['emails_as_spam']); ?></h4>
                                <ul>
                                    <?php $__currentLoopData = $emailDocContent['troubleshooting']['emails_as_spam_tips']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($tip); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                
                                <h4><?php echo e($emailDocContent['troubleshooting']['configuration_issues']); ?></h4>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <?php echo e(__('email_providers.not_available')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .markdown-content h1 { font-size: 2rem; margin-bottom: 1rem; }
    .markdown-content h2 { font-size: 1.75rem; margin-top: 2rem; margin-bottom: 1rem; }
    .markdown-content h3 { font-size: 1.5rem; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .markdown-content h4 { font-size: 1.25rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .markdown-content p { margin-bottom: 1rem; }
    .markdown-content ul, .markdown-content ol { margin-bottom: 1rem; padding-left: 2rem; }
    .markdown-content table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
    .markdown-content table th, .markdown-content table td { padding: 0.5rem; border: 1px solid #dee2e6; }
    .markdown-content pre { background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; overflow-x: auto; }
    .markdown-content code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.875em; }
    .markdown-content pre code { padding: 0; background-color: transparent; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/anchor-scroll.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/email-documentation.blade.php ENDPATH**/ ?>