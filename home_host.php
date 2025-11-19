<?php
// home_host.php
session_start();

// Lógica de sessão
// if (!isset($_SESSION['host_id'])) { header("Location: cadastrarouentrar.html"); exit; }

$nome = $_SESSION['host_name'] ?? 'Anfitrião';

// Função auxiliar para gerar iniciais
function gerar_iniciais($nome) {
    $partes = explode(' ', trim($nome));
    $p1 = $partes[0][0] ?? '';
    $p2 = $partes[1][0] ?? '';
    return strtoupper($p1 . $p2);
}
$iniciais = gerar_iniciais($nome);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Painel do Anfitrião — Patas Pousada</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Parkinsans:wght@300..800&display=swap');

  /* === CSS GERAL === */
  :root{
    --brand:#e2725b; --brand-strong:#d45f47; --ink:#333; --muted:#777; --bg:#f7f7f7; --white:#fff;
    --radius:12px; --shadow:0 6px 14px rgba(0,0,0,.08); --line:#ececec;
    --chip:#fff4f1; --chip-border:#ffd8cf;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Nunito,system-ui,Arial,sans-serif;background:var(--bg);color:var(--ink)}
  a{color:inherit; text-decoration:none;}

  /* Header */
  header{display:flex;justify-content:center;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,.1); position:sticky; top:0; z-index:10;}
  nav{max-width:1200px;width:100%;padding:16px 20px;display:flex;align-items:center;gap:20px}
  .logo{display:flex;align-items:center;text-decoration:none;color:var(--brand);font-family:Parkinsans,sans-serif;font-weight:800}
  .logo img{width:30px;height:30px;margin-right:10px}
  nav ul{list-style:none;display:flex;gap:22px;margin:0 0 0 auto;padding:0; align-items: center;}
  nav ul a{font-family:Parkinsans,sans-serif;font-weight:700;text-decoration:none;color:#222}
  nav ul a:hover{color:var(--brand)}
  .pill{color:#d74f4f}

  /* User Menu */
  .user-menu{position:relative}
  .user-trigger{display:flex;align-items:center;gap:10px;cursor:pointer}
  .avatar-circle {
      width:32px; height:32px; border-radius:50%; background:var(--chip); border:1px solid var(--chip-border);
      color:var(--brand); font-weight:800; display:flex; align-items:center; justify-content:center; font-size: 0.85rem;
  }
  .user-trigger svg{width:14px;height:14px;fill:#666;transition:transform .2s}
  .user-menu:hover .user-trigger svg{transform:rotate(180deg)}
  .dropdown{position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:var(--shadow);width:220px;opacity:0;visibility:hidden;transform:translateY(6px);transition:.15s;z-index:30}
  .user-menu:hover .dropdown{opacity:1;visibility:visible;transform:translateY(0)}
  .dropdown a{display:flex;gap:10px;padding:10px 12px;text-decoration:none;color:#333;font-weight:800; align-items: center;}
  .dropdown a:hover{background:#f7f7f7}

  /* Container */
  .wrap{max-width:1200px;margin:28px auto;padding:0 20px}

  /* Hello + Mini-cards */
  .hello{color:#666;text-align:center;margin-bottom:12px}
  .hello strong{color:var(--brand)}

  .mini-grid{margin:0 auto 28px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;max-width:1000px}
  .mini-card{
    display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #eee;border-radius:14px;padding:12px 14px;
    box-shadow:0 2px 8px rgba(0,0,0,.06);text-decoration:none;transition:transform .08s, box-shadow .2s; cursor: pointer;
  }
  .mini-card:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(0,0,0,.08)}
  .mini-icon{width:38px;height:38px;border-radius:12px;background:var(--chip);border:1px solid var(--chip-border);display:grid;place-items:center;color:var(--brand)}
  .mini-text b{display:block;font-weight:800}
  .mini-text small{color:#777}

  /* Layout */
  .cols{display:grid;grid-template-columns:260px minmax(0,1fr) 280px;gap:22px}
  @media (max-width:1024px){ .cols{grid-template-columns:1fr} }

  /* Cards */
  .card{background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
  .card .hd{padding:14px 16px;border-bottom:1px solid var(--line);font-weight:800;color:#444}
  .filters{padding:12px 12px 16px;display:flex;flex-direction:column;gap:10px}
  
  .menu-btn{
    display:flex;align-items:center;gap:10px;border:1px solid transparent; color:#555; background:#fff; border-radius:8px;
    padding:10px 12px;font-weight:700;cursor:pointer;transition:all .15s;text-decoration:none
  }
  .menu-btn:hover{background:#fff1ee; color:var(--brand);}
  .menu-btn.active{background:var(--brand);color:#fff}
  .menu-btn i { width: 20px; text-align: center; }

  /* Feed */
  .feed{display:flex;flex-direction:column;gap:16px}
  .notif-card {
    background:#fff;border:1px solid #eee;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05); padding: 20px;
    display: flex; gap: 15px; align-items: flex-start;
  }
  .notif-icon {
      width: 45px; height: 45px; background: #f4f4f5; border-radius: 50%; display: grid; place-items: center; color: #666; flex-shrink: 0;
  }
  .notif-content h4 { margin: 0 0 5px 0; font-family: 'Parkinsans', sans-serif; color: #333; }
  .notif-content p { margin: 0 0 10px 0; font-size: 0.9rem; color: #666; line-height: 1.4; }
  .btn-outline {
      border: 1px solid var(--line); background: #fff; padding: 6px 12px; border-radius: 20px; 
      font-size: 0.85rem; font-weight: 700; color: #555; cursor: pointer;
  }
  .btn-outline:hover { border-color: var(--brand); color: var(--brand); background: #fff1ee; }

  /* Right Help */
  .help{padding:14px 16px}
  .help h4{margin:0 0 8px;font-weight:800}
  .help p{margin:0 0 10px;color:#666; font-size: 0.9rem;}
  .help a{color:var(--brand);font-weight:800;text-decoration:none; font-size: 0.9rem;}
  
  .btn-danger {
      width: 100%; background: #fff; color: #b91c1c; border: 1px solid #b91c1c; 
      padding: 8px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px; transition: 0.2s;
  }
  .btn-danger:hover { background: #b91c1c; color: #fff; }

  /* === MODAL BONITA (PRETTY CONFIRM) === */
  .pp-wrap{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;align-items:center;justify-content:center;z-index:60}
  .pp-card{width:min(460px,92vw);background:#fff;border-radius:18px;box-shadow:0 30px 80px rgba(0,0,0,.35);overflow:hidden;transform:translateY(8px);opacity:0;transition:.18s}
  .pp-card.show{transform:translateY(0);opacity:1}
  .pp-card header{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #f1f5f9;background:#fff1f0}
  .pp-card header .ic{width:24px;height:24px;display:grid;place-items:center;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:900; font-size:0.9rem;}
  .pp-card h4{margin:0;font-weight:800;color:#991b1b; font-family: 'Parkinsans', sans-serif;}
  .pp-body{padding:16px; color: #444; font-size: 0.95rem;}
  .pp-body p { margin-bottom: 10px; line-height: 1.5; }
  .pp-ft{display:flex;gap:10px;justify-content:flex-end;padding:12px 14px;background:#fafafa;border-top:1px solid #eef2f7}
  .pp-btn{border-radius:12px;padding:10px 16px;font-weight:800;border:1px solid #e2e8f0;background:#fff;cursor:pointer; font-family: 'Nunito', sans-serif;}
  .pp-btn:hover { background: #f8f9fa; }
  .pp-ok{background:#b91c1c;color:#fff;border-color:#b91c1c}
  .pp-ok:hover{background:#991b1b; border-color:#991b1b;}

</style>
</head>
<body>

<header>
  <nav>
    <a href="index.html" class="logo">
      <img src="https://images.vexels.com/media/users/3/202255/isolated/preview/a095b3fe28f3c9febbf176089e2d7e08-pegada-de-cachorro-com-osso-de-coracao-rosa-plana.png" alt="">
      <span>Patas Pousada</span>
    </a>

    <ul>
      <li><a href="#">Hóspedes</a></li>
      <li><a href="#">Calendário</a></li>
      <li><a href="ajuda.html">Ajuda</a></li>
      
      <li class="user-menu">
        <div class="user-trigger">
          <div class="avatar-circle"><?= $iniciais ?></div>
          <strong style="font-family: 'Parkinsans', sans-serif; font-size: 0.95rem;"><?= htmlspecialchars($nome) ?></strong>
          <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5H7z"></path></svg>
        </div>
        <div class="dropdown">
          <a href="#"><i class="fa-solid fa-user-pen"></i> Editar Perfil</a>
          <a href="#"><i class="fa-solid fa-house-chimney"></i> Meu Espaço</a>
          <div style="height:1px;background:#eee;margin:4px 0"></div>
          <a href="logout.php" style="color:#b91c1c;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
        </div>
      </li>
    </ul>
  </nav>
</header>

<div class="wrap">
  
  <div class="hello">
    Painel do Anfitrião &bull; Bem-vindo de volta, <strong><?= htmlspecialchars($nome) ?></strong>! 🏠
  </div>

  <div class="mini-grid">
    <div class="mini-card">
      <div class="mini-icon"><i class="fa-regular fa-bell"></i></div>
      <div class="mini-text"><b>Solicitações</b><small>0 pendentes</small></div>
    </div>
    <div class="mini-card">
      <div class="mini-icon"><i class="fa-solid fa-paw"></i></div>
      <div class="mini-text"><b>Hóspedes Ativos</b><small>Nenhum pet no momento</small></div>
    </div>
    <div class="mini-card">
      <div class="mini-icon"><i class="fa-solid fa-wallet"></i></div>
      <div class="mini-text"><b>Ganhos (Mês)</b><small>R$ 0,00</small></div>
    </div>
    <div class="mini-card">
      <div class="mini-icon"><i class="fa-solid fa-star"></i></div>
      <div class="mini-text"><b>Avaliação</b><small>Novo Anfitrião (0)</small></div>
    </div>
  </div>

  <div class="cols">
    
    <div class="card">
      <div class="hd">Menu Principal</div>
      <div class="filters">
        <div class="menu-btn active"><i class="fa-solid fa-chart-pie"></i> Visão Geral</div>
        <div class="menu-btn"><i class="fa-regular fa-calendar"></i> Calendário</div>
        <div class="menu-btn"><i class="fa-solid fa-inbox"></i> Mensagens</div>
        <div class="menu-btn"><i class="fa-solid fa-sliders"></i> Configurações</div>
      </div>
    </div>

    <div class="feed">
      <div class="notif-card">
        <div class="notif-icon"><i class="fa-solid fa-camera"></i></div>
        <div class="notif-content">
           <h4>Melhore seu perfil</h4>
           <p>Seu perfil ainda não tem fotos do ambiente. Perfis com fotos recebem 5x mais pedidos.</p>
           <button class="btn-outline">Adicionar Fotos</button>
        </div>
      </div>

      <div class="notif-card">
        <div class="notif-icon"><i class="fa-regular fa-calendar-check"></i></div>
        <div class="notif-content">
           <h4>Calendário Desatualizado</h4>
           <p>Confirme sua disponibilidade para o próximo feriado para aparecer no topo das buscas.</p>
           <button class="btn-outline">Atualizar Agenda</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="hd">Dica rápida</div>
      <div class="help">
        <h4>Tempo de Resposta</h4>
        <p>Anfitriões que respondem em menos de 1 hora ganham destaque na plataforma.</p>
        <a href="ajuda.html">Ver Central de Ajuda →</a>
        
        <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">
        
        <h4 style="color:#b91c1c;">Zona de Perigo</h4>
        <p style="font-size:0.8rem">Deseja encerrar suas atividades?</p>
        
        <button class="btn-danger" onclick="openDeleteModal()">Excluir minha conta</button>
      </div>
    </div>
  </div>
</div>

<div class="pp-wrap" id="ppDelete">
  <div class="pp-card" role="dialog" aria-modal="true">
    <header>
      <span class="ic"><i class="fa-solid fa-triangle-exclamation"></i></span>
      <h4>Excluir sua conta</h4>
    </header>
    <div class="pp-body">
      <p>Esta ação é <strong>irreversível</strong>. Todos os seus dados, histórico de hospedagens e perfil serão apagados permanentemente.</p>
      <p>Para confirmar, por favor digite <strong>EXCLUIR</strong> abaixo:</p>
      <input id="delConfirmText" type="text" placeholder="EXCLUIR" style="width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;margin:6px 0 10px; font-weight:bold; color:#b91c1c;">
    </div>
    <div class="pp-ft">
      <button class="pp-btn" id="delCancel">Cancelar</button>
      <button class="pp-btn pp-ok" id="delOk">Sim, excluir tudo</button>
    </div>
  </div>
</div>

<script>
  const ppDelete = document.getElementById('ppDelete');
  const delCancel = document.getElementById('delCancel');
  const delOk = document.getElementById('delOk');
  const delInput = document.getElementById('delConfirmText');

  // Abrir Modal
  function openDeleteModal(){
    ppDelete.style.display='flex';
    // Pequeno delay para a animação funcionar
    setTimeout(() => {
        ppDelete.querySelector('.pp-card').classList.add('show');
    }, 10);
  }

  // Fechar Modal
  function closeDeleteModal(){
    ppDelete.querySelector('.pp-card').classList.remove('show');
    setTimeout(()=>{ 
        ppDelete.style.display='none'; 
        delInput.value = ''; // Limpa o campo
    }, 200);
  }

  // Eventos
  delCancel.addEventListener('click', closeDeleteModal);
  
  // Fecha se clicar fora do cartão
  ppDelete.addEventListener('click', (e)=>{ 
      if(e.target.id==='ppDelete') closeDeleteModal(); 
  });

  // Lógica de Confirmação
  delOk.addEventListener('click', () => {
      if (delInput.value === 'EXCLUIR') {
          // Redireciona para o PHP que apaga
          window.location.href = 'processaExclusao.php';
      } else {
          alert('Por favor, digite a palavra EXCLUIR corretamente para confirmar.');
          delInput.focus();
      }
  });
</script>

</body>
</html>