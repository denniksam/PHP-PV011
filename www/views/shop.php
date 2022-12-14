<h2>Магазин</h2>
Показывать товары по 
<form>
<select name="sort">
    <option value=1 <?= @$view_data[ 'sort' ] == 1 ? 'selected' : '' ?> >Новизне</option>
    <option value=2 <?= @$view_data[ 'sort' ] == 2 ? 'selected' : '' ?> >Цене</option>
    <option value=3 <?= @$view_data[ 'sort' ] == 3 ? 'selected' : '' ?> >Рейтингу</option>
</select>
<button>Применить</button>
</form>

<?php foreach( $view_data[ 'products' ] as $product ) : ?>
<div class="product" data-id="<?=$product['id']?>" >
    <div class="img-container" >
        <img src="/images/<?= $product['image'] ?>" />
    </div>
    <h4><?= $product['name'] ?></h4>
    <h5><?= $product['descr'] ?></h5>
    <b><?= $product['price'] ?></b>
    <?php if( ! empty( $product['discount'] ) ) : ?>
        (<i><?= $product['discount'] ?></i>)
    <?php endif ?>
    <div class="rating-area">
        <span>(<?= $product['rating'] ?>)</span>
        <input type="radio" id="star-5<?=$product['id']?>" name="rating<?=$product['id']?>" value="5" <?= ($product['rating'] > 4) ? 'checked' : '' ?> />
        <label for="star-5<?=$product['id']?>" title="Grade «5»"></label>
        <input type="radio" id="star-4<?=$product['id']?>" name="rating<?=$product['id']?>" value="4" <?= ($product['rating'] > 3 && $product['rating'] <= 4) ? 'checked' : '' ?> />
        <label for="star-4<?=$product['id']?>" title="Grade «4»"></label>
        <input type="radio" id="star-3<?=$product['id']?>" name="rating<?=$product['id']?>" value="3" <?= ($product['rating'] > 2 && $product['rating'] <= 3) ? 'checked' : '' ?> />
        <label for="star-3<?=$product['id']?>" title="Grade «3»"></label>
        <input type="radio" id="star-2<?=$product['id']?>" name="rating<?=$product['id']?>" value="2" <?= ($product['rating'] > 1 && $product['rating'] <= 2) ? 'checked' : '' ?> />
        <label for="star-2<?=$product['id']?>" title="Grade «2»"></label>
        <input type="radio" id="star-1<?=$product['id']?>" name="rating<?=$product['id']?>" value="1" <?= ($product['rating'] <= 1) ? 'checked' : '' ?> />
        <label for="star-1<?=$product['id']?>" title="Grade «1»"></label>
    </div>
    <u>Since <?= date( "d.m.y", strtotime( $product['add_dt'] ) ) ?></u>
 </div>
<?php endforeach ?>
<?php
    $href_base = ( isset( $view_data[ 'sort' ] ) ) 
                ? "?sort=" . $view_data[ 'sort' ] . "&"
                : "?" ;
?>
<div class='paginator'>
    <?php if( $view_data['paginator']['page'] > 1 ) : ?>
        <a href="<?=$href_base?>page=<?= $view_data['paginator']['page'] - 1 ?>">&lArr;</a>
    <?php else : ?>
        <span>&lArr;</span>
    <?php endif ?>

    <?php for( $i = 1; $i <= $view_data['paginator']['lastpage']; $i++ ) : 
        if( $i == $view_data['paginator']['page'] ) : ?>
            <b><?= $i ?></b>
        <?php else : ?>
            <a href="<?=$href_base?>page=<?= $i ?>"><?= $i ?></a> 
        <?php endif ?>
    <?php endfor ?>

    <?php if($view_data['paginator']['page'] < $view_data['paginator']['lastpage']) : ?>
        <a href="<?=$href_base?>page=<?= $view_data['paginator']['page'] + 1 ?>">&rArr;</a>
    <?php else : ?>
        <span>&rArr;</span>
    <?php endif ?>
</div>



<form method="post" enctype="multipart/form-data" >
    <input type="text"   name="name"     placeholder="Название" /><br/>
    <textarea            name="descr"    placeholder="Описание" ></textarea><br/>
    <input type="number" name="price"    placeholder="Цена" /><br/>
    <input type="number" name="discount" placeholder="Скидка" /><br/>
    <input type="file"   name="image"  /><br/>
    <button>Добавить</button>
</form>

<?= $view_data[ 'add_error' ] ?? ''  ?>

<?php

/*
Д.З. Создать контроллер shop_controller, реализовать прием данных формы
добавления товара, обработать сохранить файл-картинку, подготовить
(и выполнить) запрос на добавление данных в БД.

CREATE TABLE Products (
    `id`        CHAR(36)       NOT NULL   PRIMARY KEY     COMMENT 'UUID',
    `id_grp`    CHAR(36)           NULL                   COMMENT 'Group ID',
    `name`      VARCHAR(128)   NOT NULL,                  
    `descr`     TEXT           NOT NULL                   COMMENT 'Product description',
    `price`     DECIMAL(10,2)  NOT NULL,                  
    `discount`  TINYINT            NULL                   COMMENT 'Product discount in percent',
    `image`     VARCHAR(64)    NOT NULL                   COMMENT 'Product image filename',
    `rating`    DECIMAL(2,1)       NULL                   COMMENT '0..5 star-rating ',
    `votes`     INT            DEFAULT 0                  COMMENT 'Rating votes count',
    `add_dt`    DATETIME       DEFAULT CURRENT_TIMESTAMP  COMMENT 'Adding moment'
) ENGINE = InnoDB, DEFAULT CHARSET UTF8
*/
