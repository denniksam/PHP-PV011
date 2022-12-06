<?php if( is_array( $_CONTEXT[ 'auth_user' ] ) ) { ?>
    <b>Hello, <?= $_CONTEXT[ 'auth_user' ][ 'name' ] ?></b>
<a href="/profile/<?= $_CONTEXT['auth_user']['login'] ?>">
    <img class='user-avatar' src='/avatars/<?= empty($_CONTEXT['auth_user']['avatar']) ? 'no-avatar.png' : $_CONTEXT['auth_user']['avatar'] ?>' />
</a>
    <?php  // проверка на неподтвержденную почту, показ поля для кода
        if( $_CONTEXT[ 'auth_user' ][ 'confirm' ] != null ) {
            // почта не подтверждена ?>
        <input id='confirm-code' />
        <input type='button' value="Ok" onclick="confirmCode()" />
        <script>
            function confirmCode() {
                window.location = "/confirm?code=" +
                document.getElementById('confirm-code').value ;
            }
        </script>
    <?php }
    ?>
    <!-- Кнопка выхода из авторизованного режима - ссылка передающая параметр "logout" -->
    <a class="logout" href="?logout">Log out</a>
<?php } else {  ?>
    <form method="post">
        <label><input name="userlogin" placeholder="login" /></label>
        <label><input name="userpassw" type="password" /></label>
        <button>Log in</button>
    </form>
    <?php if( isset( $_CONTEXT[ 'auth_error' ] ) ) { echo $_CONTEXT[ 'auth_error' ] ; } ?>
    <a href="/register">Регистрация</a>
<?php }  ?>