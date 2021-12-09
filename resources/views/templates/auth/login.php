<div class="col-4">
    <form action="<?php echo BASE_URL; ?>/login/do" method="post">

        <div class="mb-3">
            <label for="username-or-email">Username / Email</label>
            <input type="text" name="username-or-email" id="username-or-email" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
        <a href="<?php echo BASE_URL; ?>/sign-up" class="btn btn-link">Registrieren</a>
    </form>
</div>
