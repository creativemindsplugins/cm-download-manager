<div class="cm-wizard-step step-0">
    <h1>Welcome to the Download Manager Setup Wizard</h1>
    <p>Thank you for installing the CM Download Manager plugin!</p>
    <p>With this plugin, you can manage, organize, and share downloadable files on your website. It also allows logged in<br>
        users to upload their own files, making it easy for everyone to contribute and access content.
    </p>
    <img class="img" src="<?php echo CMDM_SetupWizard::$wizard_url . 'assets/img/wizard_logo.png';?>">
    <p>To help you get started, we’ve prepared a quick setup wizard to guide you through these steps:</p>
    <ul>
        <li>• Configuring essential settings</li>
        <li>• Customizing the download pages</li>
        <li>• Configuring access control settings</li>
    </ul>
    <button class="next-step" data-step="0">Start</button>
    <p><a href="<?php echo admin_url( 'admin.php?page='. CMDM_SetupWizard::$setting_page_slug ); ?>" >Skip the setup wizard</a></p>
</div>
<?php echo CMDM_SetupWizard::renderSteps(); ?>