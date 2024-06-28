<section class="section index">
    <h2 class="index-title">Про гру</h2>
    <h3 class="index-subtitle">Підзаголовок</h3>
    <div class="userdata" id="userdata"></div>
    <div class="index-text">
        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Atque totam sequi praesentium doloribus quo tenetur, maxime animi aliquid est maiores ab facere rerum voluptates odio deleniti sit? Consequuntur optio inventore dolore beatae corporis doloribus ea repellendus voluptatibus cumque deserunt! Suscipit praesentium tenetur sunt quaerat dolores alias ipsam placeat tempore similique voluptatem, vel minus, quasi fugiat cupiditate distinctio aut, aspernatur animi autem aperiam corporis. Dicta, maxime odio dolorem eius voluptatum laboriosam nesciunt, consequuntur assumenda totam delectus quas tenetur optio quibusdam cumque voluptas ducimus voluptatibus ex autem iusto veniam iste tempore, soluta corporis. Facere dolorum ut laboriosam rem iste nulla ipsum tempora.
    </div>
    <div>
        <h3>SERVER:</h3>
        <p>
        <pre>
                <? var_dump($_SERVER) ?>
            </pre>
        </p>
    </div>
    <div>
        <h3>POST:</h3>
        <p>
        <pre>
            <? var_dump($_POST) ?>
            </pre>
        </p>
    </div>
    <div>
        <h3>GET:</h3>
        <p>
        <pre>
            <? var_dump($_GET) ?>
        </pre>
        </p>
    </div>
    <div>
        <h3>PHP_init:</h3>
        <p>
            <? trim(file_get_contents('php://input')) ?>
        </p>
    </div>
</section>