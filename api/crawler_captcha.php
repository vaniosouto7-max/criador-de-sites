<?php
// Proteção Anti-Crawler com reCAPTCHA Fake e Desafio de Imagens
if (isset($_POST['solve_captcha'])) {
    $_SESSION['captcha_solved_' . $_GET['produto']] = true;
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Segurança</title>
    <style>
        body { background: #f5f5f5; font-family: Roboto, "Helvetica Neue", Arial, sans-serif; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .overlay { position: fixed; inset: 0px; z-index: 99999; display: flex; align-items: center; justify-content: center; background: rgb(245, 245, 245); }

        /* Box Inicial */
        .captcha-box { width: 100%; max-width: 400px; background: #fff; border-radius: 3px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); overflow: hidden; }
        .padding-20 { padding: 24px 20px; }
        .row-captcha { display: flex; align-items: center; gap: 16px; border: 1px solid #d3d3d3; border-radius: 3px; padding: 12px 16px; background: #f9f9f9; cursor: pointer; }
        .checkbox-box { width: 28px; height: 28px; border: 2px solid #c1c1c1; border-radius: 3px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; position: relative; }
        .check-mark { display: none; color: #009688; font-size: 24px; font-weight: bold; }
        .captcha-text { font-size: 14px; color: #202124; }
        .captcha-logo { margin-left: auto; display: flex; flex-direction: column; align-items: center; gap: 2px; }
        .re-text { font-size: 8px; color: #555; letter-spacing: 0.5px; }

        /* Box de Desafio */
        .challenge-box { display: none; width: 100%; max-width: 400px; background: #fff; border-radius: 3px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); overflow: hidden; }
        .challenge-header { background: #4285f4; padding: 16px 20px; color: #fff; }
        .challenge-header .small { font-size: 14px; font-weight: 500; margin-bottom: 4px; }
        .challenge-header .big { font-size: 24px; font-weight: 700; }
        .challenge-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; padding: 2px; background: #e0e0e0; }
        .grid-item { position: relative; aspect-ratio: 1/1; cursor: pointer; overflow: hidden; }
        .grid-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.15s; }
        .grid-item.selected img { transform: scale(0.8); }
        .grid-item.selected::after { content: "✓"; position: absolute; top: 5px; left: 5px; width: 20px; height: 20px; background: #4285f4; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
        .challenge-footer { display: flex; align-items: center; justify-content: flex-end; padding: 12px 16px; border-top: 1px solid #e0e0e0; }
        .btn-verify { padding: 10px 24px; font-size: 14px; font-weight: 600; color: #fff; background: #a0c4ff; border: none; border-radius: 3px; cursor: not-allowed; transition: background 0.2s; }
        .btn-verify.active { background: #4285f4; cursor: pointer; }
    </style>
</head>
<body>
    <div class="overlay">
        <!-- ETAPA 1: Clique inicial -->
        <div class="captcha-box" id="box-step1">
            <div class="padding-20">
                <div class="row-captcha" onclick="openChallenge()">
                    <div class="checkbox-box">
                        <span class="check-mark" id="check-mark">✓</span>
                    </div>
                    <span class="captcha-text">Não sou um robô</span>
                    <div class="captcha-logo">
                        <svg height="32" viewBox="0 0 64 64" width="32">
                            <path d="M32 2C15.4 2 2 15.4 2 32s13.4 30 30 30 30-13.4 30-30S48.6 2 32 2z" fill="#4285F4"></path>
                            <path d="M32 12c-11 0-20 9-20 20s9 20 20 20 20-9 20-20-9-20-20-20z" fill="#fff"></path>
                            <path d="M32 17c-8.3 0-15 6.7-15 15s6.7 15 15 15 15-6.7 15-15-6.7-15-15-15z" fill="#4285F4" opacity="0.3"></path>
                            <text fill="#4285F4" font-size="16" font-weight="bold" text-anchor="middle" x="32" y="38">✓</text>
                        </svg>
                        <span class="re-text">reCAPTCHA</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ETAPA 2: Desafio de Imagens -->
        <div class="challenge-box" id="box-step2">
            <div class="challenge-header">
                <div class="small">Selecione todas as imagens com</div>
                <div class="big">cachorros</div>
            </div>
            <div class="challenge-grid">
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/bird1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/flower1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/cat1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/bike1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, true)"><img src="assets/captcha/dog4.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, true)"><img src="assets/captcha/dog3.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/car1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, false)"><img src="assets/captcha/flower1.jpg"></div>
                <div class="grid-item" onclick="toggleSelect(this, true)"><img src="assets/captcha/dog1.jpg"></div>
            </div>
            <div class="challenge-footer">
                <form method="POST" id="final-form">
                    <input type="hidden" name="solve_captcha" value="1">
                    <button type="button" class="btn-verify" id="btn-verify" onclick="verify()">Verificar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedCount = 0;

        function openChallenge() {
            document.getElementById('box-step1').style.display = 'none';
            document.getElementById('box-step2').style.display = 'block';
        }

        function toggleSelect(el, isCorrect) {
            el.classList.toggle('selected');
            selectedCount = document.querySelectorAll('.grid-item.selected').length;
            const btn = document.getElementById('btn-verify');
            if (selectedCount > 0) {
                btn.classList.add('active');
                btn.disabled = false;
            } else {
                btn.classList.remove('active');
                btn.disabled = true;
            }
        }

        function verify() {
            if (selectedCount > 0) {
                document.getElementById('box-step2').style.display = 'none';
                document.getElementById('box-step1').style.display = 'block';
                document.getElementById('check-mark').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('final-form').submit();
                }, 800);
            }
        }
    </script>
</body>
</html>
