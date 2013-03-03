<?php
foreach($providers as $p)
{ ?>
    <div class="provider" id="<?php echo $p->slug; ?>">
        <h2><?php echo $p->name; ?></h2>
        <ul><?php
        foreach($accounts[$p->id] as $a)
        {?>
            <li>
            </li><?php
        }?>
        </ul>
        <div class="buttons">
            <a href="#add" class="btn green">&plus; Add Account</a>
        </div>
    </div><?php
}