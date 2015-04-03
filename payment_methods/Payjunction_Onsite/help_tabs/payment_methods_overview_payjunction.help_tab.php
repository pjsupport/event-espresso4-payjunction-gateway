<h3><?php _e("PayJunction Trinity Settings", "event_espresso"); ?></h3>
<p><?php _e('Please see below for details on each option in the module settings', 'event_espresso');?></p>
<ul>
    <li>
        <strong><?php _e('API Login & Password', 'event_espresso'); ?></strong>
        <p><?php _e('For instructions on retrieving your API login and resetting your API password, please see <a href="https://company.payjunction.com/pages/viewpage.action?pageId=328435">this guide</a>', 'event_espresso'); ?></p>
    </li>
    <li>
        <strong><?php _e('Test Mode', 'event_espresso'); ?></strong>
        <p><?php _e('When enabled, sends all transactions to the <a href="https://www.payjunctionlabs.com">PayJunction Labs</a> testing site. Please see <a href="https://company.payjunction.com/support/PayJunction+Demo+Account">this guide</a> for login information.'); ?></p>
    </li>
    <li>
        <strong><?php _e('Use Local AVS', 'event_espresso'); ?></strong>
        <p><?php _e('Tells the module to either use the local setting saved in this module for the <a href="">AVS match type</a> or to use the remote settings saved directly in the PayJunction Virtual Terminal','event_espresso'); ?></p>
    </li>
    <li>
        <strong><?php _e('Address Verification Security Settings','event_espresso'); ?></strong>
        <p>
            Sets the requirement for allowing a transaction to go through to prevent fraud:
            <ul>
                <li>Address AND Zip - Requires BOTH the street address and the zip code to match the bank's records for the card holder.</li>
                <li>Address OR Zip - Requires EITHER the street address OR the zip code to match the bank's records for the card holder.</li>
                <li>Address Only - Requires a match on the street address only. *Choosing this option can raise your rates, consider Address OR Zip instead.</li>
                <li>Zip Only - Requires a match on the zip code only. *Choosing this option can raise your rates, consider Address OR Zip instead.</li>
                <li>Bypass - Records the AVS match results in the transaction data but does not automatically void the transaction whatever the result of the check.</li>
                <li>Off - This disables AVS checks entirely. *Choosing this option can raise your rates, consider Bypass instead.</li>
            </ul>
        </p>
    </li>
    <li>
        <strong><?php _e('Disable CVV Security', 'event_espresso'); ?></strong>
        <p>Setting this option to Yes removes the requirement to provide the security code on the back of the card. Please note, many banks now require this information to be sent and will not approve the transaction if CVV has been disabled.</p>
    </li>
</ul>