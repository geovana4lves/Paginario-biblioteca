<?php
require_once 'auth.php';

// Verificar se o usuário está logado
verificarLogin();

// Obter dados do usuário logado
$usuario_logado = obterUsuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Livros – Filtro de Pesquisa</title>
  <link rel="stylesheet" href="img">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Georgia, serif;
      background-color: #ecb87f;
    }

    header {
  background: #86541c;
  position: relative;
  padding: 5px 20px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center; 
}

header h1 {
  margin: 0;
  font-size: 30px;
  color: #fff;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
  position: absolute;
  left: 50%;
  transform: translateX(-50%); 
}



.search-container {
  position: absolute;
  right: 20px;
  display: flex;
  align-items: center;
}

.search-container input {
  padding: 6px 12px 6px 12px;
  font-size: 14px;
  border-radius: 20px;
  border: none;
  outline: none;
  width: 200px;
}

        .menu-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .menu-icon img {
        width: 40px;
        height: 40px;
        cursor: pointer;
    }

    .side-menu {
        position: fixed;
        top: 0;
        left: -250px;
        width: 250px;
        height: 100%;
        background-color: #86541c;
        padding-top: 60px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.5);
        transition: left 0.3s ease;
        z-index: 1000;
    }

    .side-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .side-menu ul li {
        margin: 20px 0;
        text-align: center;
    }

    .side-menu ul li a {
        color: #eab97f;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.1em;
    }

.side-menu ul li a:hover {
  color: #ffffff;
  transition: color 0.3s ease, background-color 0.3s ease;
}

    .side-menu.open {
        left: 0;
    }

    .close-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-icon img {
        width: 100%;
        height: 100%;
    }

    h1 {
      font-size:30px;
      margin: 0;
      color: #fff;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.6); 
    }

    h2 {
      margin: 5px 0 20px;
      font-size: 22px;
      text-transform: uppercase;
      color: #562f05;
    }

    .publisher-banner {
      background: url('img/image.png') no-repeat center top;
      color: #fff;
      background-size: cover;
      height: 80px;
      margin-bottom: 10px;
      width:100vw;
      display:flex;
      justify-content: center;
      align-items: center;
      position: relative;
      cursor: pointer;
      transition: transform 0.2s ease; 
    }

    .publisher-banner:active {
      transform: scale(0.98);
    }

    .icon-wrapper {
      background-color: #3d2815;
      border-radius: 50%;
      padding: 10px;
      position: absolute;
      left: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      height: 60px;
    }

    .book-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 36px;
      padding: 20px;
      border-radius: 8px;
      justify-items: center;
    }

    .book-card {
      text-align: center;
    }

    .book-card h3 {
      font-family: 'Georgia', serif;
      font-size: 1rem;
      margin-bottom: 10px;
      color: #5b3d1f;
    }

    .cover-container {
      position: relative;
      display: inline-block;
      transition: transform 0.3s ease;
      cursor: pointer;
    }

    .cover-container:hover {
      transform: scale(1.1);
      z-index: 1;
    }
.cover-container img {
  width: 240px;   
  height: 360px; 
  border-radius: 4px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  transition: transform 0.1s ease, box-shadow 0.3s ease;
  object-fit: cover; 
}

    .cover-container:hover img {
      box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }

    .cover-container:active {
      transform: scale(0.95) rotate(-2deg);
    }

    .badge {
      position: absolute;
      top: 6px;
      right: 6px;
      background-color: #e63946;
      color: #fff;
      font-size: 0.75rem;
      padding: 4px 6px;
      border-radius: 50%;
      box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .main-footer {
      text-align: center;
      padding: 14px 0;
  background: #86541c;
      color: #fff;
      font-size: 0.9rem;
    }

    .main-footer a {
      color: #fff;
      text-decoration: none;
      margin: 0 6px;
    }
header {
  background: #86541c;
  text-align: center;
  position: relative;
  padding: 5px 0;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
}

header input[type="text"] {
  padding: 5px 10px;
  font-size: 1rem;
  border-radius: 4px;
  border: none;
  outline: none;
  width: 250px;
}
#myInput {
  width: 250px;
  font-size: 16px;
  padding: 12px 20px 12px 40px; 
  border: 1px solid #ddd;
  border-radius: 4px;
  margin-bottom: 20px;
  background-image: url('img/magnifying-glass-1976105_1280.png'); 
  background-position: 10px center; 
  background-repeat: no-repeat;
  background-size: 18px 18px; 
}

#myInput:focus {
  outline: none;
  border-color: #86541c;
}
  </style>
</head>

<body>
<header>
  <div class="menu-icon">   
    <img src="img/component 1.svg" alt="Abrir Menu" />
  </div>

  <h1>FILTRO DE PESQUISA</h1>

 <div class="search-container">
  <input type="text" id="myInput" placeholder="Pesquisar Livros..." title="Digite o nome do Livro">
  </button>
</div>
</header>
<script>
function myFunction() {
    var input, filter, cards, h2, img, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    cards = document.getElementsByClassName("book-card");

    for (i = 0; i < cards.length; i++) {
        h2 = cards[i].getElementsByTagName("h2")[0];
        img = cards[i].getElementsByTagName("img")[0];
        
        txtValue = "";
        if (h2) txtValue += h2.textContent || h2.innerText;
        if (img) txtValue += " " + (img.alt || "");

        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}

document.getElementById("myInput").addEventListener("keyup", myFunction);
</script>
  
  <nav id="side-menu" class="side-menu">
    <div class="close-icon">
    <img src="img/component 1.svg" alt="Fechar Menu"/>
    </div>
    <ul>
      <li><a href="inicio.html" style="color: antiquewhite;">Página Inicial</a></li>
      <li><a style="color: peru;">------------------------------</a></li>
      <li><a style="color: antiquewhite;">Filtros</a></li>
      <li><a href="genero-literario.html">Gênero</a></li>
      <li><a href="autores.php">Autor</a></li>
      <li><a href="editora.html">Editora</a></li>
      <li><a href="faixaetaria.html">Faixa Etária</a></li>
      <li><a style="color: peru;">------------------------------</a></li>
      <li><a href="solicitacao.php" style="color: antiquewhite;">Solicitação de livros</a></li>
      <li><a style="color: peru;">------------------------------</a></li>
      <li><a href="meuperfil.php" style="color: antiquewhite;">Meu Perfil</a></li>
    </ul>
  </nav>

 
  <section class="publisher-section">
    <div class="publisher-banner">
      <div class="icon-wrapper">
        <img src="./img/paginario.png" alt="Logo Paginário" class="book-icon" style="width: 50px; height: 50px;">
      </div>
      <h1>LIVROS</h1>
    </div>

    <div class="book-grid">
    
      <div class="book-card">
        <a href="livros/memoriaspostumas.html" style="text-decoration: none; color: inherit;">
          <h2>Memórias Póstumas de Brás Cubas</h2><br>
          <div class="cover-container">
            <img src="./img/memoriasPostumas.png" alt="Memórias Póstumas de Brás Cuba">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>
     
      <div class="book-card">
        <a href="livros/melhorescontos.html" style="text-decoration: none; color: inherit;">
          <h2>Seus Trinta Melhores Contos </h2><br><br>
          <div class="cover-container">
            <img src="./img/trintaMelhores.png" alt="Seus Trinta Melhores Contos">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>
    
      <div class="book-card">
        <a href="livros/domcasmurro.html" style="text-decoration: none; color: inherit;">
          <h2>Dom Casmurro</h2><br><br>
          <div class="cover-container">
            <img src="./img/domCasmurro.png" alt="Dom Casmurro">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>
    
      <div class="book-card">
        <a href="livros/ocortico.html" style="text-decoration: none; color: inherit;">
          <h2> O Cortiço</h2><br><br>
          <div class="cover-container">
            <img src="./img/cortico.png" alt="O Cortiço">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>
      
      <div class="book-card">
        <a href="livros/maoeluva.html" style="text-decoration: none; color: inherit;">
          <h2>A Mão e a Luva</h2><br>
          <div class="cover-container">
            <img src="./img/maoLuva.png" alt="A Mão e a Luva">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>
      
      <div class="book-card">
        <a href="livros/quincasborba.html" style="text-decoration: none; color: inherit;">
          <h2>Quincas Borbas</h2><br>
          <div class="cover-container">
            <img src="./img/quincasBorba.png" alt="Quincas Borba">
            <span class="badge">12+</span>
          </div>
        </a>
      </div>
    
      <div class="book-card">
        <a href="livros/oalienista.html" style="text-decoration: none; color: inherit;">
          <h2>O Alienista</h2><br>
          <div class="cover-container">
            <img src="./img/alienista.png" alt="O Alienista">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>
    
      <div class="book-card">
        <a href="livros/helena.html" style="text-decoration: none; color: inherit;">
          <h2>Helena</h2><br>
          <div class="cover-container">
            <img src="./img/helena.png" alt="Helena">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/1984.html" style="text-decoration: none; color: inherit;">
          <h2>1984</h2><br><br>
          <div class="cover-container">
            <img src="./img/1984.jpg" alt="1984">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/RomeueJulieta.html" style="text-decoration: none; color: inherit;">
          <h2> Romeu e Julieta</h2><br><br>
          <div class="cover-container">
            <img src="./img/RomeueJulieta.jpg" alt="Romeu e Julieta">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>
<div class="book-card">
        <a href="livros/Oslusiadas.html" style="text-decoration: none; color: inherit;">
          <h2>Os Lusíadas</h2><br><br>
          <div class="cover-container">
            <img src="./img/Oslusiadas.jpg" alt="Os Lusíadas">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/sargento.html" style="text-decoration: none; color: inherit;">
          <h2>Memórias de um Sargento de Milícias</h2><br>
          <div class="cover-container">
            <img src="img/sargento.jpg" alt="O Sargento">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/arazao.html" style="text-decoration: none; color: inherit;">
          <h2>Razão e Sensibilidade</h2><br>
          <div class="cover-container">
            <img src="img/razao.jpg" alt="Razão e Sensibilidade">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

      <div class="book-card">
        <a href="livros/DomQuixote.html" style="text-decoration: none; color: inherit;">
          <h2>Dom Quixote</h2><br>
          <div class="cover-container">
            <img src="img/DomQuixote.png" alt="Dom Quixote">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

      <div class="book-card">
        <a href="livros/orgulhoepreconceito.html" style="text-decoration: none; color: inherit;">
          <h2>Orgulho e Preconceito</h2><br>
          <div class="cover-container">
            <img src="img/OrgulhoePreconceito.png  "   alt="Orgulho e Preconceito">
      
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/oguarani.html" style="text-decoration: none; color: inherit;">
          <h2>O Guarani</h2><br>
          <div class="cover-container">
            <img src="img/OGuarani.png" alt="O Guarani">
            <span class="badge">12+</span>
          </div>
        </a>
      </div>

<div class="book-card">
        <a href="livros/asviagens.html" style="text-decoration: none; color: inherit;">
          <h2>As Viagens de Gulliver</h2><br>
          <div class="cover-container">
            <img src="img/asviagens.png" alt="As Viagens">
            <span class="badge">10+</span>
          </div>
        </a>
      </div>

      <div class="book-card">
        <a href="livros/amoreamizade.html" style="text-decoration: none; color: inherit;">
          <h2>Amor e Amizade</h2><br>
          <div class="cover-container">
            <img src="img/amoreamizade.jpg" alt="Amor e Amizade">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>


         <div class="book-card">
        <a href="livros/iliada.html" style="text-decoration: none; color: inherit;">
          <h2>Ilíada</h2><br>
          <div class="cover-container">
            <img src="img/iliada.jpg" alt="Ilíada">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

         <div class="book-card">
        <a href="livros/Iracema.html" style="text-decoration: none; color: inherit;">
          <h2>Iracema</h2><br>
          <div class="cover-container">
            <img src="img/Iracema.png" alt="Iracema">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

         <div class="book-card">
        <a href="livros/osmiseraveis.html" style="text-decoration: none; color: inherit;">
          <h2>Os Miseravéis</h2><br>
          <div class="cover-container">
            <img src="img/71L28YvPobL._SY425_.jpg" alt="O Alienista">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

  <div class="book-card">
        <a href="livros/adaoeeva.html" style="text-decoration: none; color: inherit;">
          <h2>Adão e Eva</h2><br>
          <div class="cover-container">
            <img src="img/adaoeeva.png" alt="Adão e Eva">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/odisseia.html" style="text-decoration: none; color: inherit;">
          <h2>Odisseia</h2><br>
          <div class="cover-container">
            <img src="img/odisseia.png" alt="Odisseia">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/senhora.html" style="text-decoration: none; color: inherit;">
          <h2>Senhora</h2><br>
          <div class="cover-container">
            <img src="img/senhora.jpg" alt="Senhora">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>


         <div class="book-card">
        <a href="livros/sertoes.html" style="text-decoration: none; color: inherit;">
          <h2>Os Sertões</h2><br>
          <div class="cover-container">
            <img src="img/sertoes_.jpg" alt="Os Sertões">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

  <div class="book-card">
        <a href="livros/amoreperdicao.html" style="text-decoration: none; color: inherit;">
          <h2>Amor de Perdição</h2><br>
          <div class="cover-container">
            <img src="img/amoreperdicao.png" alt="Amor e Perdição">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/metamorfose.html" style="text-decoration: none; color: inherit;">
          <h2>A Metamorfose</h2><br>
          <div class="cover-container">
            <img src="img/ametamorfose.png" alt="A Metamorfose">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/osmaias.html" style="text-decoration: none; color: inherit;">
          <h2>Os Maias</h2><br>
          <div class="cover-container">
            <img src="img/osmaias.png" alt="Os Maias">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>




         <div class="book-card">
        <a href="livros/per.html" style="text-decoration: none; color: inherit;">
          <h2>Persuasão</h2><br><br>
          <div class="cover-container">
            <img src="img/per.png" alt="Persuasão">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

  <div class="book-card">
        <a href="livros/amoreninha.html" style="text-decoration: none; color: inherit;">
          <h2>A Moreninha</h2><br><br>
          <div class="cover-container">
            <img src="img/amoreninha.png" alt="A Moreninha">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/aescrava.html" style="text-decoration: none; color: inherit;">
          <h2>A Escrava Isaura</h2><br><br>
          <div class="cover-container">
            <img src="img/aescrava.png" alt="A Escrava">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/osventosruivantes.html" style="text-decoration: none; color: inherit;">
          <h2>O Morro dos Ventos Uivantes</h2><br>
          <div class="cover-container">
            <img src="img/osventosruivantes.jpg" alt="Os Ventos Ruivantes">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>


      
         <div class="book-card">
        <a href="livros/lady.html" style="text-decoration: none; color: inherit;">
          <h2>Lady Susan</h2><br>
          <div class="cover-container">
            <img src="img/lady.jpg" alt="Lady">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

  <div class="book-card">
        <a href="livros/Abby.html" style="text-decoration: none; color: inherit;">
          <h2>Northanger Abby</h2><br>
          <div class="cover-container">
            <img src="img/Northanger Abbey _.jpg" alt="Abby">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/artepoetica.html" style="text-decoration: none; color: inherit;">
          <h2>Arte Poética</h2><br>
          <div class="cover-container">
            <img src="img/artepoetica.jpg" alt="Arte Poetica">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/omercadodeveneza.html" style="text-decoration: none; color: inherit;">
          <h2>O Mercado de Veneza</h2><br>
          <div class="cover-container">
            <img src="img/mercador_de_veneza_.jpg" alt="Mercado de Veneza">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>


      
         <div class="book-card">
        <a href="livros/macbethr.html" style="text-decoration: none; color: inherit;">
          <h2>Macbethr</h2><br><br>
          <div class="cover-container">
            <img src="img/mac.png" alt="Macbethr">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

  <div class="book-card">
        <a href="livros/sofocles.html" style="text-decoration: none; color: inherit;">
          <h2>Édipo Rei</h2><br><br>
          <div class="cover-container">
            <img src="img/sofocles.jpg" alt="Sofocles">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/cartapero.html" style="text-decoration: none; color: inherit;">
          <h2>Carta de Pero Vaz de Caminha</h2><br>
          <div class="cover-container">
            <img src="img/acartapero.png" alt="A Carta">
            <span class="badge">14+</span>
          </div>
        </a>
      </div>

        <div class="book-card">
        <a href="livros/otristefim.html" style="text-decoration: none; color: inherit;">
          <h2>O Triste Fim de Policarpo Quaresma</h2><br>
          <div class="cover-container">
            <img src="img/otristefim.png" alt="Triste Fim">
            <span class="badge">16+</span>
          </div>
        </a>
      </div>

    </div>
  </section>
<br>
  <footer class="main-footer">
    <a href="politicaprivacidade.html">Política de Privacidade</a> |
    <a href="politicaprivacidade.html">Termos de Uso</a> |
    <span>Todos os direitos reservados (BR)</span>
  </footer>

  <script>
    const menuIcon = document.querySelector('.menu-icon');
    const sideMenu = document.getElementById('side-menu');
    const closeIcon = document.querySelector('.close-icon');

    menuIcon.addEventListener('click', () => {
      sideMenu.classList.add('open'); 
    });

    closeIcon.addEventListener('click', () => {
      sideMenu.classList.remove('open');
    });
  </script>

</body>
</html>
