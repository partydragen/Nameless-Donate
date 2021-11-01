{include file='header.tpl'}

<body id="page-top">

<!-- Wrapper -->
<div id="wrapper">

    <!-- Sidebar -->
    {include file='sidebar.tpl'}

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

        <!-- Main content -->
        <div id="content">

            <!-- Topbar -->
            {include file='navbar.tpl'}

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">{$SETTINGS}</h1>
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                        <li class="breadcrumb-item active">{$DONATE}</li>
                        <li class="breadcrumb-item active">{$SETTINGS}</li>
                    </ol>
                </div>

                <!-- Update Notification -->
                {include file='includes/update.tpl'}

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <!-- Success and Error Alerts -->
                        {include file='includes/alerts.tpl'}

                        <form action="" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="inputPaypalId">{$PAYPAL_EMAIL}</label>
                                        <input class="form-control" type="email" id="inputPayPalEmail" name="paypal_email" value="{$PAYPAL_EMAIL_VALUE}" placeholder="{$PAYPAL_EMAIL}">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="link_location">{$LINK_LOCATION}</label>
                                        <select class="form-control" id="link_location" name="link_location">
                                            <option value="1"{if $LINK_LOCATION_VALUE eq 1} selected{/if}>{$LINK_NAVBAR}</option>
                                            <option value="2"{if $LINK_LOCATION_VALUE eq 2} selected{/if}>{$LINK_MORE}</option>
                                            <option value="3"{if $LINK_LOCATION_VALUE eq 3} selected{/if}>{$LINK_FOOTER}</option>
                                            <option value="4"{if $LINK_LOCATION_VALUE eq 4} selected{/if}>{$LINK_NONE}</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="inputIcon">{$ICON}</label>
                                        <input type="text" class="form-control" name="icon" id="inputIcon" placeholder="{$ICON_EXAMPLE}" value="{$ICON_VALUE}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="link_location">{$CURRENCY}</label>
                                        <select class="form-control" id="currency" name="currency">
                                            {foreach from=$CURRENCY_LIST item=currency}
                                                <option value="{$currency}"{if $CURRENCY_VALUE eq {$currency}} selected{/if}>{$currency}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="inputMinAmount">{$MIN_AMOUNT}</label>
                                        <input class="form-control" type="number" id="inputMinAmount" name="min_amount" value="{$MIN_AMOUNT_VALUE}">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="link_location">{$REWARD_GROUP}</label>
                                        <select class="form-control" id="reward_group" name="reward_group">
                                            <option value="0"{if $REWARD_GROUP_VALUE == 0} selected{/if}>{$NONE}</option>
                                            {foreach from=$GROUPS item=group}
                                                <option value="{$group.id}"{if $REWARD_GROUP_VALUE == {$group.id}} selected{/if}>{$group.name}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="inputContent">{$CONTENT}</label>
                                        <textarea name="content" id="inputContent">{$CONTENT_VALUE}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="inputSuccessContent">{$SUCCESS_CONTENT}</label>
                                        <textarea name="success_content" id="inputSuccessContent">{$SUCCESS_CONTENT_VALUE}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <input type="hidden" name="token" value="{$TOKEN}">
                                        <input type="submit" value="{$SUBMIT}" class="btn btn-primary">
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <center><p>Donate Module by <a href="https://partydragen.com/" target="_blank">Partydragen</a></p></center>
                    </div>
                </div>

                <!-- Spacing -->
                <div style="height:1rem;"></div>

                <!-- End Page Content -->
            </div>

            <!-- End Main Content -->
        </div>

        {include file='footer.tpl'}

        <!-- End Content Wrapper -->
    </div>

    <!-- End Wrapper -->
</div>

{include file='scripts.tpl'}

</body>
</html>