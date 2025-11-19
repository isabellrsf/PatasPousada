<?php
// home_tutor.php
session_start();
require __DIR__ . '/auth.php';       // garante que só entra logado
require __DIR__ . '/supabase.php';   // para buscar perfil se faltar nome

// --- 1. PEGA NOME DO TUTOR --- //
$nomeTutor  = $_SESSION['full_name']  ?? null;
$idUsuario  = $_SESSION['profile_id'] ?? null;

// Se ainda não tiver o nome em sessão mas tiver o id do perfil, busca no Supabase
if (!$nomeTutor && $idUsuario) {
    $q = http_build_query([
        'id'     => 'eq.' . $idUsuario,
        'select' => 'full_name'
    ]);
    list($st, $perfil) = sb_request('GET', "/rest/v1/profiles?$q");

    if ($st === 200 && is_array($perfil) && !empty($perfil[0]['full_name'])) {
        $nomeTutor = $perfil[0]['full_name'];
        $_SESSION['full_name'] = $nomeTutor; // cache em sessão
    }
}

// Fallback final
if (!$nomeTutor) {
    $nomeTutor = 'Tutor(a)';
}

// Gera iniciais p/ avatar (server-side, caso queira usar no futuro)
function gerar_iniciais($nome) {
    $nome = trim($nome);
    if ($nome === '') return '🙂';
    $partes = preg_split('/\s+/', $nome);
    $p1 = isset($partes[0]) ? mb_substr($partes[0], 0, 1, 'UTF-8') : '';
    $p2 = isset($partes[1]) ? mb_substr($partes[1], 0, 1, 'UTF-8') : '';
    return mb_strtoupper($p1 . $p2, 'UTF-8');
}
$iniciais = gerar_iniciais($nomeTutor);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Área do Tutor — Patas Pousada</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Parkinsans:wght@300..800&display=swap');

  :root{
    --brand:#e2725b; --brand-strong:#d45f47; --ink:#333; --muted:#777; --bg:#f7f7f7; --white:#fff;
    --radius:12px; --shadow:0 6px 14px rgba(0,0,0,.08); --line:#ececec;
    --chip:#fff4f1; --chip-border:#ffd8cf;
  }
  *{box-sizing:border-box}
  body{margin:0;font-family:Nunito,system-ui,Arial,sans-serif;background:var(--bg);color:var(--ink)}
  a{color:inherit}

  /* Header */
  header{display:flex;justify-content:center;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,.1)}
  nav{max-width:1200px;width:100%;padding:16px 20px;display:flex;align-items:center;gap:20px}
  .logo{display:flex;align-items:center;text-decoration:none;color:var(--brand);font-family:Parkinsans,sans-serif;font-weight:800}
  .logo img{width:30px;height:30px;margin-right:10px}
  nav ul{list-style:none;display:flex;gap:22px;margin:0 0 0 auto;padding:0;align-items:center}
  nav ul a{font-family:Parkinsans,sans-serif;font-weight:700;text-decoration:none;color:#222}
  nav ul a:hover{color:var(--brand)}
  .pill{color:#d74f4f}

  /* user menu */
  .user-menu{position:relative}
  .user-trigger{display:flex;align-items:center;gap:10px;cursor:pointer}
  .user-trigger img{width:32px;height:32px;border-radius:50%;border:1px solid #eee;background:#f2f2f2;object-fit:cover}
  .user-trigger svg{width:14px;height:14px;fill:#666;transition:transform .2s}
  .user-menu:hover .user-trigger svg{transform:rotate(180deg)}
  .dropdown{position:absolute;top:calc(100% + 6px);right:0;background:#fff;border:1px solid #e6e6e6;border-radius:8px;box-shadow:var(--shadow);width:220px;opacity:0;visibility:hidden;transform:translateY(6px);transition:.15s;z-index:30}
  .user-menu:hover .dropdown{opacity:1;visibility:visible;transform:translateY(0)}
  .dropdown a{display:flex;gap:10px;padding:10px 12px;text-decoration:none;color:#333;font-weight:800;align-items:center}
  .dropdown a:hover{background:#f7f7f7}

  /* container */
  .wrap{max-width:1200px;margin:28px auto;padding:0 20px}

  /* hello + mini-cards */
  .hello{color:#666;text-align:center;margin-bottom:12px}
  .hello strong{color:var(--brand)}

  .mini-grid{margin:0 auto 28px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;max-width:1000px}
  .mini-card{
    display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #eee;border-radius:14px;padding:12px 14px;
    box-shadow:0 2px 8px rgba(0,0,0,.06);text-decoration:none;transition:transform .08s, box-shadow .2s;
  }
  .mini-card:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(0,0,0,.08)}
  .mini-icon{width:38px;height:38px;border-radius:12px;background:var(--chip);border:1px solid var(--chip-border);display:grid;place-items:center;color:var(--brand)}
  .mini-text b{display:block;font-weight:800}
  .mini-text small{color:#777}

  /* 3-col layout */
  .cols{display:grid;grid-template-columns:260px minmax(0,1fr) 280px;gap:22px}
  @media (max-width:1024px){ .cols{grid-template-columns:1fr} }

  /* left: filters */
  .card{background:#fff;border:1px solid #eee;border-radius:14px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
  .card .hd{padding:14px 16px;border-bottom:1px solid var(--line);font-weight:800;color:#444}
  .filters{padding:12px 12px 16px;display:flex;flex-direction:column;gap:10px}
  .chip-btn{
    display:flex;align-items:center;gap:10px;border:2px solid var(--brand);color:var(--brand);background:#fff;border-radius:999px;
    padding:8px 12px;font-weight:800;cursor:pointer;transition:all .15s;text-decoration:none
  }
  .chip-btn:hover{background:#fff1ee}
  .chip-btn.active{background:var(--brand);color:#fff}
  .select{width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:10px}

  /* center: timeline/feed */
  .feed{display:flex;flex-direction:column;gap:16px}
  .host{
    background:#fff;border:1px solid #eee;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);
    overflow:hidden;display:grid;grid-template-columns:160px 1fr 120px;gap:0
  }
  .host .photo{height:100%;min-height:130px;background:#eee}
  .host .photo img{width:100%;height:100%;object-fit:cover}
  .host .body{padding:14px 16px}
  .host .body h3{margin:0 0 6px;font:800 18px Parkinsans,sans-serif;color:#333}
  .meta{display:flex;flex-wrap:wrap;gap:10px 14px;color:#666;font-size:14px}
  .meta .tag{display:inline-flex;align-items:center;gap:8px;background:#fafafa;border:1px solid #eee;border-radius:999px;padding:4px 10px}
  .rating{color:#f2a300;font-weight:800}
  .host .cta{
    border-left:1px solid var(--line);display:flex;flex-direction:column;justify-content:center;align-items:center;padding:12px;gap:8px
  }
  .price{font-weight:900}
  .btn{
    all:unset;display:inline-block;background:#fff;color:var(--brand);border:2px solid var(--brand);padding:8px 12px;border-radius:999px;font-weight:900;cursor:pointer;text-align:center
  }
  .btn:hover{background:var(--brand);color:#fff}
  .empty{padding:16px;text-align:center;color:#777}

  /* right */
  .help{padding:14px 16px}
  .help h4{margin:0 0 8px;font-weight:800}
  .help p{margin:0 0 10px;color:#666}
  .help a{color:var(--brand);font-weight:800;text-decoration:none}

  /* modal */
  .modal{position:fixed;inset:0;display:none;place-items:center;background:rgba(0,0,0,.55);z-index:50}
  .modal.show{display:grid}
  .modal .box{background:#fff;border-radius:16px;max-width:560px;width:92%;box-shadow:var(--shadow);overflow:hidden}
  .box .box-hd{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid var(--line)}
  .box .box-hd h3{margin:0;font:800 18px Parkinsans,sans-serif}
  .box .box-bd{padding:16px}
  .x{cursor:pointer;border:none;background:transparent;font-size:20px}

  /* Pretty Confirm — Excluir Conta (UI) */
  .pp-wrap{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;align-items:center;justify-content:center;z-index:60}
  .pp-card{width:min(460px,92vw);background:#fff;border-radius:18px;box-shadow:0 30px 80px rgba(0,0,0,.35);overflow:hidden;transform:translateY(8px);opacity:0;transition:.18s}
  .pp-card.show{transform:translateY(0);opacity:1}
  .pp-card header{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #f1f5f9;background:#fff1f0}
  .pp-card header .ic{width:20px;height:20px;display:grid;place-items:center;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:900}
  .pp-card h4{margin:0;font-weight:900;color:#991b1c}
  .pp-body{padding:16px}
  .pp-ft{display:flex;gap:10px;justify-content:flex-end;padding:12px 14px;background:#fafafa;border-top:1px solid #eef2f7}
  .pp-btn{border-radius:12px;padding:9px 14px;font-weight:900;border:2px solid #e2e8f0;background:#fff;cursor:pointer}
  .pp-ok{background:#b91c1c;color:#fff;border-color:#b91c1c}

  /* Toast */
  .toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#1f2937;color:#fff;padding:10px 14px;border-radius:12px;box-shadow:0 6px 18px rgba(0,0,0,.25);font-size:.92rem;display:none;z-index:70}
</style>

<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body>
<header>
  <nav>
    <a href="index.html" class="logo">
      <img src="https://images.vexels.com/media/users/3/202255/isolated/preview/a095b3fe28f3c9febbf176089e2d7e08-pegada-de-cachorro-com-osso-de-coracao-rosa-plana.png" alt="">
      <span>Patas Pousada</span>
    </a>

    <ul>
      <li><a href="#">Reservas</a></li>
      <li><a href="ajuda.html">Ajuda</a></li>
      <li><a class="pill" href="#">Lar Temporário</a></li>
      <li class="user-menu" id="userMenuRoot">
        <div class="user-trigger">
          <img id="userAvatar" alt="avatar">
          <strong id="userName"><?php echo htmlspecialchars($nomeTutor, ENT_QUOTES, 'UTF-8'); ?></strong>
          <svg viewBox="0 0 24 24"><path d="M7 10l5 5 5-5H7z"></path></svg>
        </div>
        <div class="dropdown">
          <a href="menu_perfil.html"><i class="fa-solid fa-user-pen"></i> Editar perfil</a>
          <a href="meusPets.html"><i class="fa-solid fa-paw"></i> Meus pets</a>
          <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sair</a>
          <div style="height:1px;background:#eee;margin:6px 0"></div>
          <a href="#" id="deleteAccountLink" style="color:#b91c1c;font-weight:900;">
            <i class="fa-solid fa-user-xmark"></i> Excluir conta
          </a>
        </div>
      </li>
    </ul>
  </nav>
</header>

<div class="wrap">
  <div class="hello">
    Bem-vindo(a), <strong id="helloName"><?php echo htmlspecialchars($nomeTutor, ENT_QUOTES, 'UTF-8'); ?></strong>! 🐾
  </div>

  <div class="mini-grid">
    <a class="mini-card" href="menu_perfil.html">
      <div class="mini-icon"><i class="fa-regular fa-id-card"></i></div>
      <div class="mini-text"><b>Dados pessoais</b><small>Endereço, contatos…</small></div>
    </a>
    <a class="mini-card" href="meusPets.html">
      <div class="mini-icon"><i class="fa-solid fa-paw"></i></div>
      <div class="mini-text"><b>Seus pets</b><small>Adicione/edite seus pets</small></div>
    </a>
    <a class="mini-card" href="alterar-senha.html">
      <div class="mini-icon"><i class="fa-solid fa-gear"></i></div>
      <div class="mini-text"><b>Configurações</b><small>Redefinir senha</small></div>
    </a>
  </div>

  <div class="cols">
    <div class="card">
      <div class="hd">Buscar serviços</div>
      <div class="filters">
        <button class="chip-btn active" data-service="all"><i class="fa-solid fa-magnifying-glass"></i> Todos</button>
        <button class="chip-btn" data-service="daycare">☀️ Day Care</button>
        <button class="chip-btn" data-service="hospedagem">🏠 Hospedagem</button>
        <button class="chip-btn" data-service="lar">📍 Lar Temporário</button>
        <hr style="border:none;border-top:1px dashed #eee;margin:8px 0">
        <label style="font-weight:800;color:#444">Cidade</label>
        <select class="select" id="citySelect">
          <option value="all">Todas</option>
          <option value="Plano Piloto">Plano Piloto</option>
          <option value="Taguatinga">Taguatinga</option>
          <option value="Guará">Guará</option>
          <option value="Sobradinho">Sobradinho</option>
        </select>
      </div>
    </div>

    <div id="feed" class="feed"></div>

    <div class="card">
      <div class="hd">Dica rápida</div>
      <div class="help">
        <h4>Como funciona?</h4>
        <p>Escolha o tipo de serviço na esquerda. Os perfis abaixo filtram em tempo real.</p>
        <p>Ao abrir um perfil, você vê detalhes e pode iniciar um contato.</p>
        <a href="ajuda.html">Ver Central de Ajuda →</a>
      </div>
    </div>
  </div>
</div>

<div id="modal" class="modal">
  <div class="box">
    <div class="box-hd">
      <h3 id="mName">Perfil</h3>
      <button class="x" onclick="toggleModal(false)">×</button>
    </div>
    <div class="box-bd" id="mBody"></div>
  </div>
</div>

<div class="pp-wrap" id="ppDelete" style="display:none">
  <div class="pp-card" role="dialog" aria-modal="true">
    <header>
      <span class="ic">!</span>
      <h4>Excluir sua conta</h4>
    </header>
    <div class="pp-body">
      <p>Esta ação é <strong>irreversível</strong>. Todos os seus dados (perfil, pets, reservas, arquivos) serão removidos.</p>
      <p>Para confirmar, digite <strong>EXCLUIR</strong>:</p>
      <input id="delConfirmText" type="text" placeholder="EXCLUIR" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;margin:6px 0 10px">
      <p>Por segurança, informe sua <strong>senha atual</strong> (Opcional neste momento):</p>
      <input id="delPassword" type="password" placeholder="Senha atual" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px">
    </div>
    <div class="pp-ft">
      <button class="pp-btn" id="delCancel">Cancelar</button>
      <button class="pp-btn pp-ok" id="delOk">Sim, excluir</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
  const currentUserName = "<?php echo htmlspecialchars($nomeTutor, ENT_QUOTES, 'UTF-8'); ?>";

  const SUPABASE_URL = "https://nwbgzokttjgkipwzfgzc.supabase.co";
  const SUPABASE_ANON_KEY = "sb_publishable_wn2dxmpI7gFOx4nLh5oHqg_Qlc4Q0lQ";
  const sb = window.supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

  const userMenuRoot = document.getElementById('userMenuRoot');
  const userNameEl   = document.getElementById('userName');
  const userAvatarEl = document.getElementById('userAvatar');
  const helloNameEl  = document.getElementById('helloName');

  function toast(msg){
    const t=document.getElementById('toast');
    t.textContent=msg; t.style.display='block';
    setTimeout(()=>t.style.display='none',2200);
  }

  function initials(name){
    if (!name) return "🙂";
    const parts = name.trim().split(/\s+/);
    const p1 = parts[0]?.[0] || '';
    const p2 = parts[1]?.[0] || '';
    return (p1 + p2).toUpperCase();
  }

  function updateUI(display, avatarUrl=null){
    userNameEl.textContent = display;
    helloNameEl.textContent = display;

    if (avatarUrl){
      userAvatarEl.src = avatarUrl;
      userAvatarEl.style.display = 'inline';
    } else {
      const c = document.createElement('canvas'); c.width=64; c.height=64;
      const cx = c.getContext('2d');
      cx.fillStyle='#ffe6e6'; cx.fillRect(0,0,64,64);
      cx.fillStyle='#e2725b'; cx.font='bold 26px Nunito';
      cx.textAlign='center'; cx.textBaseline='middle';
      cx.fillText(initials(display),32,34);
      userAvatarEl.src = c.toDataURL();
      userAvatarEl.style.borderRadius='50%';
      userAvatarEl.style.display='inline';
    }

    userMenuRoot.style.display = 'flex';
  }

  // ===== Dataset fake dos hosts (igual ao HTML original) =====
  const HOSTS = [
    { id:1, name:"Arthur",      city:"Plano Piloto", service:["hospedagem","daycare"], price:55, rating:5.0, reviews:47, img:"https://cdn-icons-png.flaticon.com/512/1373/1373255.png", about:"Experiente em pets de pequeno porte." },
    { id:2, name:"Jessye",      city:"Guará",        service:["lar","hospedagem"],     price:45, rating:5.0, reviews:33, img:"https://cdn-icons-png.flaticon.com/512/1373/1373254.png", about:"Especialista em pets idosos." },
    { id:3, name:"Isabella",    city:"Taguatinga",   service:["daycare","hospedagem"], price:45, rating:4.9, reviews:27, img:"https://cdn-icons-png.flaticon.com/512/4842/4842664.png", about:"Experiência com gatos e cães." },
    { id:4, name:"João Victor", city:"Sobradinho",   service:["lar"],                  price:50, rating:4.5, reviews:13, img:"https://cdn-icons-png.flaticon.com/512/4427/4427372.png", about:"Companhia para pets idosos." },
    { id:5, name:"Maria Luiza", city:"Plano Piloto", service:["daycare"],              price:45, rating:4.4, reviews:17, img:"https://cdn-icons-png.flaticon.com/512/4440/4440876.png", about:"Muito amor e cuidado." },
    { id:6, name:"Juliana",     city:"Guará",        service:["hospedagem"],           price:40, rating:4.3, reviews:11, img:"https://cdn-icons-png.flaticon.com/256/3577/3577429.png", about:"Treina e cuida de cachorros." }
  ];

  const feed = document.getElementById('feed');

  function hostCard(h){
    const services = h.service
      .map(s => ({daycare:"Day Care", hospedagem:"Hospedagem", lar:"Lar Temporário"}[s]))
      .join(", ");
    const el = document.createElement('div');
    el.className='host';
    el.dataset.city = h.city;
    el.dataset.service = h.service.join(',');

    el.innerHTML = `
      <div class="photo"><img src="${h.img}" alt="${h.name}"></div>
      <div class="body">
        <h3>${h.name} <span style="font-size:12px;color:#999">• ${h.city}</span></h3>
        <div class="meta" style="margin-top:6px">
          <span class="rating"><i class="fa-solid fa-star"></i> ${h.rating.toFixed(1)} <span style="color:#999;font-weight:600">(${h.reviews})</span></span>
          <span class="tag"><i class="fa-solid fa-briefcase"></i> ${services}</span>
          <span class="tag"><i class="fa-solid fa-comment"></i> ${h.about}</span>
        </div>
      </div>
      <div class="cta">
        <div class="price">R$ ${h.price} <small style="color:#777;font-weight:600">/ noite</small></div>
        <button class="btn" onclick='openProfile(${h.id})'>Ver perfil</button>
      </div>`;
    return el;
  }

  function render(list){
    feed.innerHTML = "";
    if (!list.length){
      feed.innerHTML = `<div class="card"><div class="empty">Nenhum perfil encontrado para o filtro.</div></div>`;
      return;
    }
    list.forEach(h => feed.appendChild(hostCard(h)));
  }

  // filtros
  let currentService = "all";
  let currentCity    = "all";

  function applyFilters(){
    const result = HOSTS.filter(h => {
      const serviceOk = currentService === "all" || h.service.includes(currentService);
      const cityOk    = currentCity === "all"    || h.city === currentCity;
      return serviceOk && cityOk;
    });
    render(result);
  }

  document.addEventListener('click', ev => {
    const b = ev.target.closest('.chip-btn');
    if (!b) return;
    document.querySelectorAll('.chip-btn').forEach(x => x.classList.remove('active'));
    b.classList.add('active');
    currentService = b.dataset.service;
    applyFilters();
  });

  document.getElementById('citySelect').addEventListener('change', e => {
    currentCity = e.target.value;
    applyFilters();
  });

  // modal perfil
  function toggleModal(show){ document.getElementById('modal').classList.toggle('show', !!show); }

  function openProfile(id){
    const h = HOSTS.find(x => x.id === id);
    if (!h) return;
    document.getElementById('mName').textContent = h.name + " • " + h.city;
    document.getElementById('mBody').innerHTML = `
      <div style="display:flex;gap:16px;align-items:center">
        <img src="${h.img}" style="width:86px;height:86px;border-radius:12px;object-fit:cover;background:#eee">
        <div>
          <div style="color:#f2a300;font-weight:800">
            <i class="fa-solid fa-star"></i> ${h.rating.toFixed(1)}
            <span style="color:#888;font-weight:600">(${h.reviews} avaliações)</span>
          </div>
          <div style="margin-top:6px;color:#666">${h.about}</div>
          <div style="margin-top:8px">
            <span class="tag"><i class="fa-solid fa-briefcase"></i>
              ${h.service.map(s => ({daycare:"Day Care",hospedagem:"Hospedagem",lar:"Lar Temporário"}[s])).join(", ")}
            </span>
          </div>
        </div>
      </div>
      <hr style="border:none;border-top:1px solid #eee;margin:14px 0">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div class="price">R$ ${h.price} <small style="color:#777;font-weight:600">/ noite</small></div>
        <button class="btn" onclick="alert('Em breve: iniciar contato/reserva')">Iniciar contato</button>
      </div>
    `;
    toggleModal(true);
  }

  document.getElementById('modal').addEventListener('click', e => {
    if (e.target.id === 'modal') toggleModal(false);
  });

  // ===== Excluir conta (apenas UI, sem backend ainda) =====
  const ppDelete       = document.getElementById('ppDelete');
  const delCancel      = document.getElementById('delCancel');
  const delOk          = document.getElementById('delOk');
  const delConfirmText = document.getElementById('delConfirmText');
  const delPassword    = document.getElementById('delPassword');

  function openDeleteModal(){
    ppDelete.style.display='flex';
    requestAnimationFrame(()=>ppDelete.querySelector('.pp-card').classList.add('show'));
  }
  function closeDeleteModal(){
    ppDelete.querySelector('.pp-card').classList.remove('show');
    setTimeout(()=>{ ppDelete.style.display='none'; },140);
    delConfirmText.value='';
    delPassword.value='';
  }

  document.getElementById('deleteAccountLink')?.addEventListener('click', e=>{
    e.preventDefault();
    openDeleteModal();
  });
  delCancel?.addEventListener('click', closeDeleteModal);
  ppDelete?.addEventListener('click', e=>{
    if (e.target.id === 'ppDelete') closeDeleteModal();
  });
  delOk?.addEventListener('click', ()=>{
    if (delConfirmText.value.trim().toUpperCase() !== 'EXCLUIR'){
      toast('Digite EXCLUIR para confirmar.');
      delConfirmText.focus();
      return;
    }
    toast('Confirmação recebida. (Depois ligamos com o backend)');
    closeDeleteModal();
  });

  // ===== Logout Supabase + sessão PHP =====
  // O logout da sessão PHP é feito em logout.php. Aqui limpamos Supabase.
  // (Se quiser forçar, você pode chamar logout.php via link.)
  // Já está no <a href="logout.php">, então não preciso interceptar o clique.

  // ===== Boot =====
  (async function init(){
    // Se quiser forçar proteção pelo Supabase também:
    try{
      const { data:{ user } } = await sb.auth.getUser();
      if (!user) {
        // Usuário não tem sessão Supabase; mas como o PHP já autenticou,
        // você pode decidir se redireciona ou só mantém a tela.
        // location.href = "cadastrarouentrar.html?next=home_tutor.php";
      }
    }catch(e){}

    // Atualiza UI usando o nome vindo do PHP (não usa e-mail!).
    updateUI(currentUserName);
    applyFilters(); // render inicial do feed
  })();
</script>
</body>
</html>
