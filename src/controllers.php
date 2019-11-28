<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));


$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', [
        'readme' => file_get_contents('README.md'),
    ]);
});


$app->match('/login', function (Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');

    if ($username) {
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";
        $user = $app['db']->fetchAssoc($sql);

        if ($user){
            $app['session']->set('user', $user);
            return $app->redirect('/todo');
        }
    }

    return $app['twig']->render('login.html', array());
});


$app->get('/logout', function () use ($app) {
    $app['session']->set('user', null);
    return $app->redirect('/');
});


$app->get('/todo/{id}', function ($id, Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $contentType = $request->headers->get('Content-Type');

    if ($id){
        //Adding the user_id clause here means that bad actors can't snoop about by providing just any id
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}'";
        $todo = $app['db']->fetchAssoc($sql);

        if (strpos($contentType, 'application/json') === false) {
            //The two pages being displayed were nigh-identical; by using a single variable we can switch between the two views and reduce
            //our code footprint.
            return $app['twig']->render('todos.html', [
                'todoId' => $todo['id'],
            ]);
        } else {
            //Was not getting right Content-Type responses with json_encode; this fixed that.
            return $app->json($todo);
        }

    } else {
        if (strpos($contentType, 'application/json') === false) {
            return $app['twig']->render('todos.html', [
                'todoId' => -1,
            ]);
        } else {
            //Moving the query in here will avoid unnecessary DB hits when just rendering the page.
            $sql = "SELECT * FROM todos WHERE user_id = '${user['id']}'";
            $todos = $app['db']->fetchAll($sql);
            return $app->json($todos);
        }
    }
})
->value('id', null);

$app->post('/todo/add', function (Request $request) use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $contentType = $request->headers->get('Content-Type');
    if (strpos($contentType, 'application/json') === false) {
        return $app->redirect('/todo');
    } else {
        $user_id = $user['id'];
        $description = $request->get('description');
        $sql = "INSERT INTO todos (user_id, description) VALUES ('$user_id', '$description')";
        $app['db']->executeUpdate($sql);

        return $app->json(array('success' => true));
    }
});


$app->match('/todo/delete/{id}', function (Request $request, $id) use ($app) {

    $contentType = $request->headers->get('Content-Type');
    if (strpos($contentType, 'application/json') === false) {
        return $app->redirect('/todo');
    } else {
        //Moved the queries here to avoid pointless DB hits when there's an incorrect Content-Type
        //Similar to requesting Todos above, if the user isn't the owner of the Todo they shouldn't be allowed to delete it.
        $sql = "DELETE FROM todos WHERE id = '$id' AND user_id = '${user['id']}'";
        $app['db']->executeUpdate($sql);
        return $app->json(array('success' => true));
    }
});


$app->match('/todo/complete/{id}', function (Request $request, $id) use ($app) {

    $contentType = $request->headers->get('Content-Type');
    if (strpos($contentType, 'application/json') === false) {
        return $app->redirect('/todo');
    } else {
        $sql = "UPDATE todos SET completed = 1 WHERE id = '$id' AND user_id = '${user['id']}'";
        $app['db']->executeUpdate($sql);
        return $app->json(array('success' => true));
    }
});
