<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/supabase.php';

$owner = $_SESSION['profile_id'];
$sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : null;

$q = http_build_query([
  'select' => '*',
  'owner_profile_id' => 'eq.' . $owner,
  'order' => 'id.desc',
]);
list($st, $pets) = sb_request('GET', "/rest/v1/pets?$q");
if ($st >= 300) { http_response_code($st); die('Erro ao carregar pets'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus Pets - Patas Pousada</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&family=Parkinsans:wght@400;700&display=swap');
    body { font-family: 'Nunito', sans-serif; background-color: #fafafa; margin: 0; color: #333; }
    header { justify-content: center; display: flex; background-color: #fff; padding: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 20px; width:100%; }
    nav .logo { font-size: 1.5em; font-weight: bold; color: #e2725b; text-decoration: none; font-family: 'Parkinsans', sans-serif; }
    nav ul { list-style: none; display: flex; gap: 15px; }
    nav ul li a { text-decoration: none; color: #333; font-weight: bold; transition: 0.3s; }
    nav ul li a:hover { color: red; }
    .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
    h2 { text-align: center; color: #e2725b; font-family: 'Parkinsans', sans-serif; font-size: 1.8em; margin-bottom: 20px; }
    .add-btn { display: inline-block; background-color: #e2725b; color: white; padding: 10px 18px; border-radius: 25px; text-decoration: none; font-weight: bold; font-family: 'Parkinsans', sans-serif; transition: all .2s; margin-bottom: 20px; }
    .add-btn:hover { background-color: #d65a47; }
    .msg-sucesso { background:#e6ffed; color:#2e7d32; padding:10px 12px; border-radius:8px; font-weight:bold; text-align:center; margin-bottom:20px; }
    .pets-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px; }
    .pet-card { background:#fff; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,.08); overflow:hidden; transition:.2s; }
    .pet-card:hover { transform: translateY(-4px); box-shadow:0 5px 14px rgba(0,0,0,.15); }
    .pet-photo { width:100%; height:180px; object-fit:cover; background:#f0f0f0; }
    .pet-info { padding:15px; text-align:center;}
    .pet-info h3 { margin:0; color:#e2725b; font-family:'Parkinsans', sans-serif; font-size:1.15em; }
    .pet-info p { margin:6px 0; color:#555; font-size:.95em; line-height:1.4; }
    .card-actions { display:flex; gap:8px; justify-content:center; padding:0 0 16px; }
    .btn { border-radius:12px; padding:8px 10px; font-weight:800; font-family:'Parkinsans',sans-serif; border:2px solid transparent; cursor:pointer; text-decoration:none; }
    .btn-edit { background:#fff; border-color:#e2725b; color:#e2725b; }
    .btn-edit:hover { background:#fff4f3; }
    .btn-del { background:#ffeceb; border-color:#ffb4a8; color:#b80000; }
    .btn-del:hover { background:#ffd9d4; }
    .empty { text-align:center; color:#777; margin:40px 0; }
    footer { text-align:center; font-size:.85em; color:#888; margin:40px 0 20px; }

    /* Pretty Confirm — tema ALERTA */
    .pp-confirm-backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);display:none;align-items:center;justify-content:center;z-index:1000}
    .pp-confirm{width:min(460px,92vw);background:#fff;color:#0f172a;border-radius:20px;box-shadow:0 30px 80px rgba(0,0,0,.35);overflow:hidden;transform:translateY(10px);opacity:0;transition:.18s;font-family:'Nunito',system-ui,Arial}
    .pp-confirm.show{transform:translateY(0);opacity:1}
    .pp-confirm header{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #f1f5f9;background:#fff1f0}
    .pp-confirm header .alert-icon{width:20px;height:20px;display:inline-grid;place-items:center;border-radius:999px;background:#fee2e2;color:#b91c1c;font-weight:900}
    .pp-confirm h3{margin:0;font-size:1.05rem;color:#991b1b}
    .ppc-close{margin-left:auto;border:none;background:transparent;cursor:pointer;font-size:1rem;opacity:.6}
    .ppc-close:hover{opacity:1}
    .ppc-body{padding:18px 16px;font-size:.98rem;line-height:1.5}
    .pp-confirm footer{display:flex;gap:10px;justify-content:flex-end;padding:12px 16px;background:#fafafa;border-top:1px solid #eef2f7}
    .ppc-cancel{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:10px 14px;cursor:pointer}
    .ppc-ok{background:#b91c1c;color:#fff;border:2px solid #b91c1c;border-radius:12px;padding:10px 14px;cursor:pointer;font-weight:800}
    .ppc-ok:hover{filter:brightness(.98)}
    .ppc-cancel:hover{background:#fff4f3}
  </style>
</head>
<body>
  <header>
    <nav>
      <a href="index.html" class="logo">Patas Pousada</a>
      <ul>
        <li><a href="home_tutor.html">Início</a></li>
        <li><a href="ajuda.html">Ajuda</a></li>
      </ul>
    </nav>
  </header>

  <div class="container">
    <h2>🐾 Meus Pets</h2>

    <?php if ($sucesso): ?>
      <div class="msg-sucesso"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <a href="cadastroPets.html" class="add-btn">➕ Cadastrar Novo Pet</a>

    <div class="pets-grid">
      <?php if (!$pets): ?>
        <p class="empty">Você ainda não cadastrou nenhum pet 😺 <a href="cadastroPets.html">Cadastrar agora</a></p>
      <?php else: ?>
        <?php foreach ($pets as $p):
          $foto = !empty($p['photo_path']) ? public_storage_url('uploads', $p['photo_path'])
               : (!empty($p['photo_url']) ? $p['photo_url'] : 'https://placehold.co/400x300?text=Pet');
        ?>
          <div class="pet-card">
            <img src="<?= htmlspecialchars($foto) ?>" class="pet-photo" alt="Foto do pet">
            <div class="pet-info">
              <h3><?= htmlspecialchars($p['name'] ?? 'Sem nome') ?></h3>
              <p><strong>Espécie:</strong> <?= htmlspecialchars($p['species'] ?? '-') ?></p>
              <?php if (!empty($p['breed'])): ?><p><strong>Raça:</strong> <?= htmlspecialchars($p['breed']) ?></p><?php endif; ?>
              <?php if (isset($p['age_years'])): ?><p><strong>Idade:</strong> <?= (int)$p['age_years'] ?> anos</p><?php endif; ?>
              <?php if (!empty($p['size'])): ?><p><strong>Porte:</strong> <?= htmlspecialchars($p['size']) ?></p><?php endif; ?>
              <?php if (!empty($p['notes'])): ?><p><strong>Obs:</strong> <?= nl2br(htmlspecialchars($p['notes'])) ?></p><?php endif; ?>
            </div>
            <div class="card-actions">
              <a class="btn btn-edit" href="editar_pet.php?id=<?= urlencode($p['id']) ?>">Editar</a>
              <form action="deletar_pet.php" method="post" style="display:inline">
                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                <button class="btn btn-del" type="submit">Excluir</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <footer>© <?= date('Y') ?> Patas Pousada • Todos os direitos reservados</footer>

  <!-- Pretty Confirm (tema ALERTA) -->
  <div class="pp-confirm-backdrop" id="ppConfirm" aria-hidden="true">
    <div class="pp-confirm" role="dialog" aria-modal="true" aria-labelledby="ppcTitle" aria-describedby="ppcMsg">
      <header>
        <span class="alert-icon">!</span>
        <h3 id="ppcTitle">Ação irreversível</h3>
        <button class="ppc-close" type="button" aria-label="Fechar">✕</button>
      </header>
      <div class="ppc-body">
        <p id="ppcMsg">Tem certeza?</p>
      </div>
      <footer>
        <button class="ppc-cancel" type="button">Cancelar</button>
        <button class="ppc-ok" type="button">Sim, excluir</button>
      </footer>
    </div>
  </div>

  <script>
    // PrettyConfirm reutilizável
    function prettyConfirm(message='Tem certeza?', {okText='Sim, excluir', cancelText='Cancelar'} = {}){
      return new Promise(resolve=>{
        const bd = document.getElementById('ppConfirm');
        const box = bd.querySelector('.pp-confirm');
        const msg = bd.querySelector('#ppcMsg');
        const btnOk = bd.querySelector('.ppc-ok');
        const btnCancel = bd.querySelector('.ppc-cancel');
        const btnClose = bd.querySelector('.ppc-close');

        msg.textContent = message; btnOk.textContent = okText; btnCancel.textContent = cancelText;

        bd.style.display = 'flex';
        requestAnimationFrame(()=> box.classList.add('show'));
        let lastFocused = document.activeElement; btnOk.focus();

        const cleanup = (val)=>{
          box.classList.remove('show');
          setTimeout(()=>{ bd.style.display='none'; }, 150);
          document.removeEventListener('keydown', onKey);
          btnOk.removeEventListener('click', onOk);
          btnCancel.removeEventListener('click', onCancel);
          btnClose.removeEventListener('click', onCancel);
          if(lastFocused) lastFocused.focus();
          resolve(val);
        };
        const onOk = ()=> cleanup(true);
        const onCancel = ()=> cleanup(false);
        const onKey = (e)=>{
          if(e.key === 'Escape') onCancel();
          if(e.key === 'Enter') onOk();
          if(e.key === 'Tab'){
            const foci = [btnOk, btnCancel, btnClose];
            const idx = foci.indexOf(document.activeElement);
            if(e.shiftKey){ if(idx <= 0){ e.preventDefault(); foci[foci.length-1].focus(); } }
            else { if(idx === foci.length-1){ e.preventDefault(); foci[0].focus(); } }
          }
        };

        btnOk.addEventListener('click', onOk);
        btnCancel.addEventListener('click', onCancel);
        btnClose.addEventListener('click', onCancel);
        document.addEventListener('keydown', onKey);
      });
    }

    // Intercepta exclusão para usar o prettyConfirm
    document.addEventListener('click', async (e)=>{
      const form = e.target.closest('form[action="deletar_pet.php"]');
      if(!form || e.target.type !== 'submit') return;

      e.preventDefault();
      const petName = (form.closest('.pet-card')?.querySelector('h3')?.textContent || 'este pet').trim();
      const ok = await prettyConfirm(`Tem certeza que deseja excluir ${petName}? Esta ação não pode ser desfeita.`);
      if(ok) form.submit();
    });
  </script>
</body>
</html>
