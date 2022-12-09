<?php 
    if( empty( $_PROF_DATA ) ) {
        echo "Профиль не найден или был удален" ;
        exit ;
    }
    $is_my_profile = ( $_PROF_DATA['login'] === $_CONTEXT['auth_user']['login'] ) ;     
?>

<div class='profile-container'>
    <h1><?= $_PROF_DATA['title'] ?></h1>
    
    <img class='profile-avatar' src='/avatars/<?= empty($_PROF_DATA['avatar']) ? 'no-avatar.png' : $_PROF_DATA['avatar'] ?>' alt="avatar">
    
    <h3>Welcome,</h4>
    
    <h2 class='profile-name'><?= $_PROF_DATA['name'] ?></h3>
    
    <table class='profile-table' >
        <tr>
            <td>Login</td>
            <td><span class='profile-input' id='user-login' <?= $is_my_profile ? 'contenteditable' : '' ?> ><?= $_PROF_DATA['login'] ?></span></td>
        </tr>
        <tr>
            <td>Name</td>
            <td><span class='profile-input' id='user-name' <?= $is_my_profile ? 'contenteditable' : '' ?>><?= $_PROF_DATA['name'] ?></span></td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td><span class='profile-input' type="email" id='user-email' <?= $is_my_profile ? 'contenteditable' : '' ?> ><?= $_PROF_DATA['email'] ?></span></td>
        </tr>
    </table>
    <?php if( $is_my_profile ) : ?>
        <button class='update-profile-btn' onclick='updateProfile()'>Update Profile</button>
        <br/>
        <button class='update-profile-btn' onclick='deleteProfile()'>Delete Profile</button>
        <script>
            function updateProfile() {
                let spanLogin = document.getElementById('user-login') ;
                if( ! spanLogin ) throw "spanLogin no found" ;
                let spanName = document.getElementById('user-name') ;
                if( ! spanName ) throw "spanName no found" ;
                let spanEmail = document.getElementById('user-email') ;
                if( ! spanEmail ) throw "spanEmail no found" ;
                fetch('/profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        "login":spanLogin.innerText,
                        "name": spanName.innerText, 
                        "email":spanEmail.innerText } )
                }).then( r => { 
                    if(r.status == 200) { window.location = window.location } 
                    else {console.log(r.status); r.text().then(console.log); }
                } ) ;
            }
            function deleteProfile() {
                fetch( '/profile', {
                    method: 'DELETE',
                }).then( r => { 
                    if(r.status == 200) { window.location = "/?logout" } 
                    else {console.log(r.status); r.text().then(console.log); }
                } ) ;
            }
        </script>
    <?php endif ?>
    <!-- + Оформить вид личного кабинета -->
</div>
