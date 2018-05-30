<?php

global $wpdb;

//获取所有注册并成功激活的用户名
$userinfos = $wpdb->get_results(
    "
    SELECT ID, user_login
    FROM $wpdb->users
    WHERE user_status = 0 "
);

//update_user_meta函数插入提交的数据
if(isset($_POST['submit'])){
    $user_id = $_POST['user_id'];
    $gpgpublickey =  $_POST['gpgpublickey'];
        if(!update_user_meta( $user_id, 'gpgpublickey', $gpgpublickey)){
            echo '更新失败';
        };
        echo '设置成功';
    }
?>
<center><br><br>
<form action="<?php echo get_option('siteurl'); ?>/wp-admin/users.php?page=user_admin_setting" method="post">
用户名:<select name="user_id">
<?php foreach($userinfos as  $userinfo){ ?>
       <option value="<?php echo $userinfo->ID;?>"><?php echo $userinfo->user_login;?></option>
<?php } ?>
</select>
<br>gpgpublickey:<br>
<textarea style="width:400px;height:200px;" type="text" name="gpgpublickey"></textarea><br />
<input type="submit" name="submit" value="更新" />
</form>
</center>

