<?php foreach($providers as $p)
{ ?>
    <div class="provider form_inputs" id="<?php echo $p->slug; ?>">
        <h2><?php echo $p->name; ?></h2>
        <ul>
            <li>
                <label>
                    <?php echo lang('accounts:field:client_key'); ?><br/>
                    <?php echo form_input('client_id', $p->client_key); ?>
                </label>
            </li>
            <li>
                <label>
                    <?php echo lang('accounts:field:client_secret'); ?><br/>
                    <?php echo form_input('client_secret', $p->client_secret); ?>
                </label>
            </li>
        </ul>
        <div class="buttons">
            <a href="#save" class="btn blue">Save</a>
            <a href="#disable" class="btn red">Disable</a>
        </div>
    </div><?php
}