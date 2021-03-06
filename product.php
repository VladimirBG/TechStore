<?php

require('lib/common.php');


/*
* Проверяет, что была выполнена отправка формы входа
*/
function is_postback()
{
    return isset($_GET['product_id']);
}

function is_postbuy()
{
    return isset($_POST['buy_product_id']);
}
/*
 * Точка входа скрипта
 */
function main()
{
    session_start();
    /* Если была выполнена отправка формы,  то открыть запрашиваемую страницу,
    * иначе открыть главную страницу
    */
    $dbh = db_connect();
    if (is_postbuy()) {
        if (is_current_user()) {
            $product = array('count' => 1, 'user_id' => $_SESSION['user_id'], 'product_id' => $_POST['buy_product_id']);
            db_product_incar_insert($dbh, $product);
        } else redirect('login.php');
    }

    if(is_postback() || is_postbuy()) {
        // обрабатываем отправленную форму

        if (is_current_user()) {
            $count_in_car = product_count_in_car($dbh);
            $car_items = db_get_product_in_car_by_user($dbh);

            /*Добавлен ли продукт в корзин пользователя? */
            /* Если корзина пустая, то в массиве хранится  значение
            Array ( [0] => Array ( [total] => 0 ) ), отсюда получается следующий оператор) исправлю потом */
            if($car_items[0]['total'] !== 0)
                foreach ($car_items as $car_item) {
                    $car_productid[] = $car_item[0]['id'];
                } else $car_productid[] = null;
        } else {
            $count_in_car = array();
            $car_productid[] = null;
        }
        /*Вывести на страницу товар id которго передан либо гетом(Когда нажали на ссылку товара из других страниц), либо постом(Когда товар купили) */
        if(is_postback()) $items_result = db_product_find_by_product_id($dbh, $_GET['product_id']);
        elseif(is_postbuy()) $items_result = db_product_find_by_product_id($dbh, $_POST['buy_product_id']);
        $category_items = db_product_find_category_all($dbh);
        db_close($dbh);

        render('Product_Page_Template', array(
            'items' => $items_result, 'category' => $category_items, 'count_in_car' => $count_in_car, 'car_productid' => $car_productid ));
    } else {
        redirect('index.php');
    }
}

main();