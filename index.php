<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

startSess();

$cu = currentUser();

$totalMembers = 12000;
$totalPaid = '$890,000';

$csrf = csrf();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="WorkupX — Professional Copy Trading & Investment Platform. Earn daily with expert signals.">
<title>WorkupX — Professional Investment Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<style>
/* ─── DESIGN SYSTEM ─────────────────────────────────────────── */
:root {
  --bg:    #070b12;
  --bg2:   #0c1220;
  --bg3:   #111929;
  --card:  #0f1824;
  --card2: #162030;
  --b:     rgba(255,255,255,0.07);
  --b2:    rgba(255,255,255,0.04);
  --gold:  #f0b90b;
  --gold2: #c9910a;
  --gd:    rgba(240,185,11,0.12);
  --grn:   #0ecb81;
  --grnd:  rgba(14,203,129,0.10);
  --red:   #f6465d;
  --redd:  rgba(246,70,93,0.10);
  --blu:   #1890ff;
  --blud:  rgba(24,144,255,0.10);
  --pur:   #8b5cf6;
  --purd:  rgba(139,92,246,0.10);
  --tx:    #eaecef;
  --tx2:   #b7bdc6;
  --tx3:   #707a8a;
  --fh:    'Space Grotesk', sans-serif;
  --fb:    'Inter', sans-serif;
  --fm:    'JetBrains Mono', monospace;
  --r:     10px;
  --rl:    16px;
  --rx:    22px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;font-size:15px;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);overflow-x:hidden;-webkit-font-smoothing:antialiased;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 100% 60% at 50% -5%,rgba(240,185,11,0.06) 0%,transparent 65%),radial-gradient(ellipse 50% 40% at 100% 60%,rgba(14,203,129,0.03) 0%,transparent 50%);pointer-events:none;z-index:0;}
::-webkit-scrollbar{width:4px;height:4px;} ::-webkit-scrollbar-track{background:var(--bg);} ::-webkit-scrollbar-thumb{background:#2a3245;border-radius:2px;}
img{max-width:100%;display:block;}
a{text-decoration:none;color:inherit;}

/* ─── NAVBAR ──────────────────────────────────────────────────── */
.nav{position:fixed;top:0;left:0;right:0;z-index:900;background:rgba(7,11,18,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--b2);height:60px;display:flex;align-items:center;padding:0 20px;justify-content:space-between;}
.nav-logo{display:flex;align-items:center;gap:0;cursor:pointer;}
.nav-logo img{height:36px;width:auto;}
.nav-right{display:flex;align-items:center;gap:8px;}
.nbtn{padding:8px 16px;border-radius:var(--r);font-family:var(--fb);font-size:0.8rem;font-weight:600;cursor:pointer;transition:all .18s;letter-spacing:.3px;}
.nbtn-out{background:transparent;border:1.5px solid rgba(240,185,11,0.4);color:var(--gold);}
.nbtn-out:hover{background:var(--gd);}
.nbtn-fill{background:var(--gold);border:1.5px solid var(--gold);color:#000;font-weight:700;}
.nbtn-fill:hover{background:var(--gold2);box-shadow:0 0 18px rgba(240,185,11,0.3);}
.nav-ticker{flex:1;overflow:hidden;margin:0 20px;display:flex;align-items:center;}
.ticker-track{display:flex;gap:32px;animation:tick 40s linear infinite;white-space:nowrap;}
@keyframes tick{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.tick-i{font-family:var(--fm);font-size:0.66rem;color:var(--tx3);display:flex;align-items:center;gap:5px;}
.tick-i .s{color:var(--tx2);font-weight:600;} .up{color:var(--grn);} .dn{color:var(--red);}

/* ─── HERO ─────────────────────────────────────────────────────── */
.hero{padding:100px 20px 60px;position:relative;overflow:hidden;text-align:center;min-height:560px;display:flex;flex-direction:column;align-items:center;justify-content:center;}
.hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(240,185,11,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(240,185,11,0.025) 1px,transparent 1px);background-size:52px 52px;pointer-events:none;}
.hero-glow1{position:absolute;top:-10%;left:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(240,185,11,0.08),transparent 70%);pointer-events:none;}
.hero-glow2{position:absolute;bottom:-10%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(14,203,129,0.06),transparent 70%);pointer-events:none;}
.hero-tag{display:inline-flex;align-items:center;gap:7px;background:var(--gd);border:1px solid rgba(240,185,11,0.22);border-radius:30px;padding:6px 16px;font-size:0.72rem;font-weight:700;color:var(--gold);letter-spacing:1px;text-transform:uppercase;margin-bottom:20px;}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--grn);animation:pls 1.5s infinite;}
@keyframes pls{0%,100%{box-shadow:0 0 0 0 rgba(14,203,129,.55)}60%{box-shadow:0 0 0 5px rgba(14,203,129,0)}}
.hero h1{font-family:var(--fh);font-size:clamp(2rem,6vw,3.4rem);font-weight:800;line-height:1.08;margin-bottom:16px;letter-spacing:-.5px;}
.hero h1 em{font-style:normal;background:linear-gradient(135deg,var(--gold) 0%,#ffd000 50%,var(--grn) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.hero p{font-size:1rem;color:var(--tx2);line-height:1.7;max-width:480px;margin:0 auto 32px;}
.hero-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:13px 26px;border-radius:var(--r);font-family:var(--fb);font-size:0.9rem;font-weight:700;cursor:pointer;transition:all .2s;border:none;letter-spacing:.3px;}
.btn-gold{background:var(--gold);color:#000;} .btn-gold:hover{background:var(--gold2);box-shadow:0 0 22px rgba(240,185,11,0.35);transform:translateY(-1px);}
.btn-out{background:transparent;border:1.5px solid var(--b);color:var(--tx2);} .btn-out:hover{border-color:var(--gold);color:var(--gold);}
.btn-grn{background:var(--grn);color:#000;} .btn-grn:hover{opacity:.88;transform:translateY(-1px);}
.btn-sm{padding:9px 18px;font-size:0.8rem;}
.btn-xs{padding:6px 12px;font-size:0.72rem;}
.btn-full{width:100%;}

/* ─── STATS BAR ────────────────────────────────────────────────── */
.stats-bar{background:linear-gradient(135deg,var(--card),var(--card2));border:1px solid rgba(240,185,11,0.15);border-radius:var(--rl);padding:20px 16px;margin:0 16px 24px;display:grid;grid-template-columns:repeat(4,1fr);gap:0;text-align:center;}
.sb-item{padding:8px 10px;border-right:1px solid var(--b2);}
.sb-item:last-child{border-right:none;}
.sb-label{font-size:.6rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;font-weight:600;}
.sb-val{font-family:var(--fh);font-size:1.05rem;font-weight:700;}

/* ─── CARD SYSTEM ──────────────────────────────────────────────── */
.card{background:var(--card);border:1px solid var(--b2);border-radius:var(--rl);}
.card-gold{border-color:rgba(240,185,11,0.2);box-shadow:0 0 28px rgba(240,185,11,0.06);}
.card-grn{border-color:rgba(14,203,129,0.2);box-shadow:0 0 28px rgba(14,203,129,0.06);}
.panel{background:var(--bg2);border:1px solid var(--b2);border-radius:var(--rl);overflow:hidden;}
.panel-hd{padding:14px 18px;border-bottom:1px solid var(--b2);display:flex;align-items:center;justify-content:space-between;}
.panel-title{font-weight:700;font-size:.85rem;display:flex;align-items:center;gap:7px;}
.panel-dot{width:7px;height:7px;border-radius:50%;background:var(--gold);animation:pls 1.5s infinite;}
.panel-bd{padding:16px 18px;}
.section{padding:0 16px;margin-bottom:28px;}
.section-head{margin-bottom:16px;}
.section-tag{display:inline-flex;align-items:center;gap:5px;background:var(--gd);border:1px solid rgba(240,185,11,0.2);border-radius:20px;padding:4px 12px;font-size:.66rem;font-weight:700;color:var(--gold);letter-spacing:.8px;text-transform:uppercase;margin-bottom:8px;}
.section-h2{font-family:var(--fh);font-size:1.5rem;font-weight:800;color:var(--tx);margin-bottom:5px;}
.section-sub{font-size:.82rem;color:var(--tx3);line-height:1.65;}

/* ─── PLAN CARDS ───────────────────────────────────────────────── */
.plans-grid{display:flex;flex-direction:column;gap:14px;}
.plan-card{background:var(--card);border:1.5px solid var(--b2);border-radius:var(--rx);padding:22px;position:relative;overflow:hidden;cursor:pointer;transition:all .25s;}
.plan-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;}
.plan-basic::before  {background:linear-gradient(90deg,#60a5fa,#818cf8);}
.plan-silver::before {background:linear-gradient(90deg,#c0c0c0,#a8a8a8);}
.plan-gold::before   {background:linear-gradient(90deg,var(--gold),#e0870a);}
.plan-card:hover{transform:translateY(-3px);border-color:rgba(240,185,11,0.3);box-shadow:0 0 30px rgba(240,185,11,0.08);}
.plan-popular{border-color:rgba(240,185,11,0.35)!important;box-shadow:0 0 30px rgba(240,185,11,0.1)!important;}
.plan-badge{position:absolute;top:14px;right:14px;background:var(--gold);color:#000;font-size:.58rem;font-weight:800;padding:3px 8px;border-radius:4px;letter-spacing:.8px;}
.plan-icon{font-size:1.8rem;margin-bottom:10px;}
.plan-name{font-size:.7rem;font-weight:700;color:var(--tx3);text-transform:uppercase;letter-spacing:2px;margin-bottom:4px;}
.plan-price{font-family:var(--fh);font-size:2rem;font-weight:800;color:var(--tx);}
.plan-price span{font-size:1rem;font-weight:400;color:var(--tx3);}
.plan-bonus{font-size:.78rem;color:var(--grn);font-weight:600;margin:4px 0 12px;}
.plan-div{height:1px;background:var(--b2);margin:12px 0;}
.plan-feat li{font-size:.78rem;color:var(--tx2);padding:3px 0;list-style:none;display:flex;align-items:flex-start;gap:7px;line-height:1.5;}
.plan-feat li::before{content:'✓';color:var(--grn);font-weight:800;font-size:.72rem;margin-top:1px;flex-shrink:0;}

/* ─── REWARD BANNERS ───────────────────────────────────────────── */
.rew-banner{border-radius:var(--rl);overflow:hidden;margin-bottom:12px;}
.rew-body{padding:18px 16px;display:flex;align-items:center;gap:14px;}
.rew-icon{font-size:3.6rem;line-height:1;filter:drop-shadow(0 4px 12px rgba(0,0,0,.5));flex-shrink:0;}
.rew-info .rew-label{font-size:.58rem;text-transform:uppercase;letter-spacing:2px;font-weight:700;margin-bottom:3px;}
.rew-info .rew-title{font-family:var(--fh);font-size:1.2rem;font-weight:800;color:#fff;line-height:1.1;margin-bottom:3px;}
.rew-info .rew-desc{font-size:.74rem;color:rgba(255,255,255,.5);}
.rew-foot{padding:10px 16px;}
.prog-bar{background:rgba(255,255,255,.07);border-radius:99px;height:5px;overflow:hidden;}
.prog-fill{height:100%;border-radius:99px;transition:width .5s;}

/* ─── BADGES ───────────────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:.65rem;font-weight:700;}
.bg-g{background:var(--grnd);color:var(--grn);border:1px solid rgba(14,203,129,.2);}
.bg-r{background:var(--redd);color:var(--red);border:1px solid rgba(246,70,93,.2);}
.bg-y{background:var(--gd)  ;color:var(--gold);border:1px solid rgba(240,185,11,.2);}
.bg-b{background:var(--blud);color:var(--blu);border:1px solid rgba(24,144,255,.2);}
.bg-p{background:var(--purd);color:var(--pur);border:1px solid rgba(139,92,246,.2);}

/* ─── FORMS ─────────────────────────────────────────────────────── */
.form-group{margin-bottom:14px;}
.form-label{display:block;font-size:.7rem;font-weight:600;color:var(--tx3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.6px;}
.form-input{width:100%;background:var(--bg3);border:1.5px solid var(--b);color:var(--tx);padding:11px 14px;border-radius:var(--r);font-family:var(--fb);font-size:.9rem;outline:none;transition:border-color .18s,box-shadow .18s;}
.form-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(240,185,11,.08);}
.form-input::placeholder{color:var(--tx3);}
select.form-input option{background:var(--bg3);}
.input-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.form-hint{font-size:.68rem;color:var(--tx3);margin-top:4px;}
.form-error{color:var(--red);font-size:.76rem;margin-top:4px;display:none;}

/* ─── STAT BOXES ────────────────────────────────────────────────── */
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.stat-box{background:var(--bg3);border:1px solid var(--b2);border-radius:var(--r);padding:14px 13px;position:relative;overflow:hidden;}
.stat-box::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;}
.sb-gold::before{background:linear-gradient(90deg,var(--gold),transparent);}
.sb-grn::before {background:linear-gradient(90deg,var(--grn),transparent);}
.sb-red::before {background:linear-gradient(90deg,var(--red),transparent);}
.sb-blu::before {background:linear-gradient(90deg,var(--blu),transparent);}
.sb-pur::before {background:linear-gradient(90deg,var(--pur),transparent);}
.sb-lbl{font-size:.64rem;color:var(--tx3);text-transform:uppercase;letter-spacing:.7px;margin-bottom:5px;font-weight:600;}
.sb-val{font-family:var(--fh);font-size:1.15rem;font-weight:700;}
.sb-val.gold{color:var(--gold);} .sb-val.grn{color:var(--grn);} .sb-val.red{color:var(--red);} .sb-val.blu{color:var(--blu);} .sb-val.pur{color:var(--pur);}
.sb-sub{font-size:.62rem;color:var(--tx3);margin-top:3px;}

/* ─── MODALS ─────────────────────────────────────────────────────── */
.mo{position:fixed;inset:0;background:rgba(0,0,0,.87);z-index:1000;display:none;align-items:center;justify-content:center;padding:16px;}
.mo.open{display:flex;}
.md{background:var(--card2);border:1px solid rgba(240,185,11,.12);border-radius:var(--rx);width:100%;max-width:460px;max-height:90vh;overflow-y:auto;padding:26px;position:relative;animation:mdin .22s cubic-bezier(.34,1.56,.64,1);}
@keyframes mdin{from{opacity:0;transform:scale(.92)translateY(10px)}to{opacity:1;transform:scale(1)translateY(0)}}
.mo-close{position:absolute;top:14px;right:14px;background:var(--bg3);border:1px solid var(--b);border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--tx3);font-size:.9rem;transition:all .15s;}
.mo-close:hover{background:var(--redd);color:var(--red);}
.mo-title{font-family:var(--fh);font-size:1rem;font-weight:700;margin-bottom:18px;}
.mo-logo{display:flex;justify-content:center;margin-bottom:16px;}
.mo-logo img{height:38px;width:auto;}

/* ─── TOAST ─────────────────────────────────────────────────────── */
.toast-wrap{position:fixed;top:70px;right:14px;z-index:2000;display:flex;flex-direction:column;gap:7px;pointer-events:none;}
.toast{background:var(--card2);border-radius:var(--r);padding:12px 16px;font-size:.82rem;min-width:230px;max-width:310px;display:flex;align-items:center;gap:9px;box-shadow:0 8px 32px rgba(0,0,0,.5);pointer-events:all;animation:tin .3s ease;border-left:3px solid var(--gold);}
@keyframes tin{from{opacity:0;transform:translateX(14px)}to{opacity:1;transform:translateX(0)}}
.toast.success{border-left-color:var(--grn);} .toast.error{border-left-color:var(--red);} .toast.info{border-left-color:var(--blu);}

/* ─── BOTTOM NAV (mobile) ────────────────────────────────────────── */
.bnav{position:fixed;bottom:0;left:0;right:0;z-index:900;background:rgba(7,11,18,.97);backdrop-filter:blur(20px);border-top:1px solid var(--b2);display:none;align-items:center;height:60px;}
@media(max-width:767px){.bnav{display:flex;}.main-content{padding-bottom:70px;}}
.nav-i{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;cursor:pointer;color:var(--tx3);font-size:.52rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:5px 4px;transition:color .15s;}
.nav-i svg{width:20px;height:20px;stroke-width:1.8;}
.nav-i:hover,.nav-i.active{color:var(--gold);}
.nav-i.active svg{filter:drop-shadow(0 0 5px rgba(240,185,11,.6));}
.nav-badge2{position:absolute;top:3px;right:calc(50% - 16px);background:var(--red);color:#fff;border-radius:10px;font-size:.5rem;font-weight:700;padding:1px 5px;border:2px solid var(--bg);}

/* ─── HOW IT WORKS ───────────────────────────────────────────────── */
.step-card{background:var(--bg2);border:1px solid var(--b2);border-radius:var(--rl);padding:16px;display:flex;gap:14px;align-items:flex-start;}
.step-num{width:38px;height:38px;border-radius:var(--r);background:var(--gd);border:1px solid rgba(240,185,11,.2);display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:.82rem;font-weight:800;color:var(--gold);flex-shrink:0;}
.step-title{font-weight:700;font-size:.9rem;margin-bottom:3px;}
.step-desc{font-size:.76rem;color:var(--tx3);line-height:1.55;}

/* ─── RISK BANNER ────────────────────────────────────────────────── */
.risk-box{background:rgba(246,70,93,.06);border:1px solid rgba(246,70,93,.18);border-radius:var(--r);padding:11px 14px;font-size:.73rem;color:var(--tx3);line-height:1.65;margin-bottom:14px;}
.risk-box strong{color:var(--red);}

/* ─── RESPONSIVE ─────────────────────────────────────────────────── */
@media(max-width:767px){
  .stats-bar{grid-template-columns:repeat(2,1fr);}
  .sb-item:nth-child(2){border-right:none;}
  .sb-item:nth-child(3){border-right:1px solid var(--b2);border-top:1px solid var(--b2);}
  .sb-item:nth-child(4){border-top:1px solid var(--b2);}
  .nav-ticker{display:none;}
  .nav-right .nbtn-out{display:none;}
}
@media(min-width:768px){
  .plans-grid{grid-template-columns:repeat(3,1fr);display:grid;}
  .section{padding:0 24px;}
  .stats-bar{margin:0 24px 28px;}
  .hero{padding:110px 24px 70px;}
}

/* ─── CHART ──────────────────────────────────────────────────────── */
.chart-box{position:relative;height:220px;}
.chart-sm{position:relative;height:80px;}

/* ─── DIVIDER ────────────────────────────────────────────────────── */
.gline{height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);margin:14px 0;opacity:.25;}
.div-text{display:flex;align-items:center;gap:10px;margin:16px 0;font-size:.72rem;color:var(--tx3);}
.div-text::before,.div-text::after{content:'';flex:1;height:1px;background:var(--b2);}

/* ─── COPY SECTION ───────────────────────────────────────────────── */
.copy-row{display:flex;align-items:center;gap:8px;background:var(--bg3);border:1px solid var(--b);border-radius:var(--r);padding:10px 14px;}
.copy-val{font-family:var(--fm);font-size:.76rem;color:var(--gold);flex:1;word-break:break-all;}

/* ─── TRADE WINDOW ───────────────────────────────────────────────── */
.trade-win{background:linear-gradient(135deg,rgba(14,203,129,.05),rgba(240,185,11,.03));border:1.5px solid rgba(14,203,129,.25);border-radius:var(--rl);padding:16px;}
.live-price{font-family:var(--fm);font-size:1.7rem;font-weight:700;color:var(--grn);}

/* ─── FAQ ────────────────────────────────────────────────────────── */
.faq-item{background:var(--bg2);border:1px solid var(--b2);border-radius:var(--r);margin-bottom:8px;overflow:hidden;}
.faq-q{padding:14px 16px;font-weight:600;font-size:.88rem;cursor:pointer;display:flex;justify-content:space-between;align-items:center;}
.faq-q .arrow{color:var(--gold);transition:transform .2s;}
.faq-q.open .arrow{transform:rotate(180deg);}
.faq-a{padding:0 16px;max-height:0;overflow:hidden;transition:max-height .3s,padding .3s;font-size:.82rem;color:var(--tx3);line-height:1.7;}
.faq-a.open{max-height:200px;padding:0 16px 14px;}

/* ─── FOOTER ─────────────────────────────────────────────────────── */
.footer{background:var(--bg2);border-top:1px solid var(--b2);padding:28px 16px;margin-top:28px;}
.footer-logo img{height:32px;width:auto;margin-bottom:10px;}
.footer-desc{font-size:.76rem;color:var(--tx3);line-height:1.65;margin-bottom:14px;}
.footer-links{display:flex;gap:14px;flex-wrap:wrap;}
.footer-links a{font-size:.76rem;color:var(--tx3);cursor:pointer;}
.footer-links a:hover{color:var(--gold);}
.footer-copy{margin-top:18px;font-size:.7rem;color:var(--tx3);padding-top:14px;border-top:1px solid var(--b2);}
</style>
</head>
<body>

<!-- ─── NAVBAR ──────────────────────────────────────────────────── -->
<nav class="nav" id="main-nav">
  <div class="nav-logo" onclick="location.href='/'">
    <img src="logo.png" alt="WorkupX">
  </div>
  <div class="nav-ticker" id="ticker-bar">
    <div class="ticker-track" id="ticker-inner"></div>
  </div>
  <div class="nav-right">
    <?php if($cu): ?>
      <button class="nbtn nbtn-out" onclick="location.href='/dashboard.php'">Dashboard</button>
      <button class="nbtn nbtn-fill" onclick="location.href='/logout.php'">Logout</button>
    <?php else: ?>
      <button class="nbtn nbtn-out" onclick="openMo('mo-login')">Log In</button>
      <button class="nbtn nbtn-fill" onclick="openMo('mo-register')">Get Started</button>
    <?php endif; ?>
  </div>
</nav>

<!-- ─── TOAST ──────────────────────────────────────────────────── -->
<div class="toast-wrap" id="toast-wrap"></div>

<?php if(!$cu): ?>
<!-- LOGIN MODAL -->
<div class="mo" id="mo-login">

  <div class="md">

    <button class="mo-close" onclick="closeMo('mo-login')">
      ✕
    </button>

    <div class="mo-logo">
      <img src="/logo.png" alt="WorkupX">
    </div>

    <div class="mo-title">
      Welcome Back
    </div>

    <div class="risk-box" style="font-size:.72rem;">
      Log in to your WorkupX dashboard to manage your portfolio.
    </div>

    <form id="login-form" method="POST" action="/login.php">

      <div class="form-group">

        <label class="form-label">
          Email Address
        </label>

        <input
          class="form-input"
          type="email"
          name="email"
          placeholder="admin@workupx.com"
          required
        >

      </div>

      <div class="form-group">

        <label class="form-label">
          Password
        </label>

        <input
          class="form-input"
          type="password"
          name="password"
          placeholder="••••••••"
          required
        >

      </div>

      <div style="text-align:right;margin-bottom:18px;">

        <span
          style="color:var(--gold);font-size:.82rem;cursor:pointer;font-weight:600;"
          onclick="closeMo('mo-login');openMo('mo-forgot')"
        >
          Forgot Password?
        </span>

      </div>

      <button type="submit" class="btn btn-gold btn-full">
        Sign In →
      </button>

      <div
        id="login-msg"
        style="font-size:.8rem;margin-top:10px;text-align:center;"
      ></div>

      <div class="div-text">
        or
      </div>

      <div
        style="text-align:center;font-size:.83rem;color:var(--tx3);"
      >

        No account?

        <span
          style="color:var(--gold);cursor:pointer;font-weight:600;"
          onclick="closeMo('mo-login');openMo('mo-register')"
        >
          Register Free
        </span>

      </div>

    </form>

  </div>

</div>

<!-- ─── REGISTER MODAL ──────────────────────────────────────────── -->
<div class="mo" id="mo-register">
  <div class="md">
    <button class="mo-close" onclick="closeMo('mo-register')">✕</button>
    <div class="mo-logo"><img src="/logo.png" alt="WorkupX"></div>
    <div class="mo-title">Create Account</div>
    <div class="risk-box">⚠ By registering you acknowledge that copy trading involves financial risk. Returns are not guaranteed.</div>
    <form id="reg-form">
      <input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="form-group"><label class="form-label">Full Name *</label><input class="form-input" type="text" name="name" placeholder="Your full name" required></div>
      <div class="form-group"><label class="form-label">Email Address *</label><input class="form-input" type="email" name="email" placeholder="your@email.com" required></div>
      <div class="form-group"><label class="form-label">Phone / WhatsApp *</label><input class="form-input" type="tel" name="phone" placeholder="+92 300 0000000" required></div>
      <div class="input-row" style="margin-bottom:14px;">
        <div><label class="form-label">Password *</label><input class="form-input" type="password" name="password" placeholder="Min 8 chars" required minlength="8"></div>
        <div><label class="form-label">Confirm *</label><input class="form-input" type="password" name="password2" placeholder="Repeat" required></div>
      </div>
      <div class="form-group"><label class="form-label">Investment Plan *</label>
        <select class="form-input" name="plan" required>
          <option value="">-- Select a Plan --</option>
          <option value="basic">🔵 Basic — $125 deposit + $12.50 welcome bonus</option>
          <option value="silver">🥈 Silver — $250 deposit + $25 welcome bonus</option>
          <option value="gold">🥇 Gold — $500 deposit + $50 welcome bonus</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Referral Code <span style="font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></label><input class="form-input" type="text" name="ref_code" placeholder="e.g. WX-ABC123"></div>
      <button type="submit" class="btn btn-gold btn-full" id="reg-btn">Create My Account →</button>
      <div id="reg-msg" style="font-size:.8rem;margin-top:8px;text-align:center;"></div>
      <div class="div-text">or</div>
      <div style="text-align:center;font-size:.83rem;color:var(--tx3);">Already registered? <span style="color:var(--gold);cursor:pointer;font-weight:600;" onclick="closeMo('mo-register');openMo('mo-login')">Sign In</span></div>
    </form>
  </div>
</div>

<!-- ─── FORGOT MODAL ────────────────────────────────────────────── -->
<div class="mo" id="mo-forgot">
  <div class="md">
    <button class="mo-close" onclick="closeMo('mo-forgot')">✕</button>
    <div class="mo-logo"><img src="/logo.png" alt="WorkupX"></div>
    <div class="mo-title">🔑 Reset Password</div>
    <div id="forgot-step1">
      <p style="font-size:.82rem;color:var(--tx3);margin-bottom:16px;">Enter your registered email to receive a verification code.</p>
      <form id="forgot-form1">
        <div class="form-group"><label class="form-label">Email Address</label><input class="form-input" type="email" name="email" placeholder="your@email.com" required></div>
        <button type="submit" class="btn btn-gold btn-full">Send Reset Code</button>
        <div id="forgot-msg1" style="font-size:.8rem;margin-top:8px;text-align:center;"></div>
      </form>
    </div>
    <div id="forgot-step2" style="display:none;">
      <div style="background:var(--grnd);border:1px solid rgba(14,203,129,.2);border-radius:var(--r);padding:13px;margin-bottom:16px;text-align:center;">
        <div style="font-size:.68rem;color:var(--tx3);margin-bottom:5px;">Your verification code:</div>
        <div id="otp-display" style="font-family:var(--fm);font-size:1.8rem;font-weight:700;color:var(--grn);letter-spacing:8px;">——</div>
        <div style="font-size:.66rem;color:var(--tx3);margin-top:3px;">(In production this is emailed to you)</div>
      </div>
      <form id="forgot-form2">
        <div class="form-group"><label class="form-label">Verification Code</label><input class="form-input" type="text" name="otp" placeholder="6-digit code" style="font-family:var(--fm);letter-spacing:4px;text-align:center;" maxlength="6" required></div>
        <div class="form-group"><label class="form-label">New Password</label><input class="form-input" type="password" name="password" placeholder="Min 8 characters" required minlength="8"></div>
        <div class="form-group"><label class="form-label">Confirm New Password</label><input class="form-input" type="password" name="password2" placeholder="Repeat new password" required></div>
        <button type="submit" class="btn btn-gold btn-full">Reset Password</button>
        <div id="forgot-msg2" style="font-size:.8rem;margin-top:8px;text-align:center;"></div>
      </form>
    </div>
    <div style="text-align:center;margin-top:14px;"><span style="font-size:.8rem;color:var(--gold);cursor:pointer;" onclick="closeMo('mo-forgot');openMo('mo-login')">← Back to Login</span></div>
  </div>
</div>
<?php endif; ?>

<!-- ─── MAIN CONTENT ────────────────────────────────────────────── -->
<main class="main-content">

<!-- HERO -->
<section class="hero">
  <div class="hero-grid"></div>
  <div class="hero-glow1"></div>
  <div class="hero-glow2"></div>
  <div style="position:relative;z-index:1;max-width:540px;">
    <div class="hero-tag"><span class="live-dot"></span>PROFESSIONAL COPY TRADING PLATFORM</div>
    <h1>Trade Smart.<br><em>Earn Every Day.</em></h1>
    <p>Join <?= number_format($totalMembers) ?>+ traders on WorkupX. Copy professional signals, earn daily commissions, and build passive income through our referral network.</p>
    <div class="hero-btns">
      <?php if($cu): ?>
        <a href="/dashboard.php" class="btn btn-gold">📊 Go to Dashboard</a>
        <a href="/trade.php" class="btn btn-out">⚡ Trade Now</a>
      <?php else: ?>
        <button class="btn btn-gold" onclick="openMo('mo-register')">🚀 Start Investing</button>
        <button class="btn btn-out" onclick="document.getElementById('plans-section').scrollIntoView({behavior:'smooth'})">📋 View Plans</button>
      <?php endif; ?>
    </div>
    <!-- Mini live price -->
    <div style="display:flex;justify-content:center;gap:16px;margin-top:22px;flex-wrap:wrap;">
      <div style="background:rgba(14,203,129,.08);border:1px solid rgba(14,203,129,.18);border-radius:var(--r);padding:8px 14px;display:flex;align-items:center;gap:8px;">
        <span class="live-dot"></span>
        <span style="font-family:var(--fm);font-size:.8rem;color:var(--grn);font-weight:600;" id="hero-btc">BTC: $67,420</span>
      </div>
      <div style="background:rgba(240,185,11,.08);border:1px solid rgba(240,185,11,.18);border-radius:var(--r);padding:8px 14px;font-size:.8rem;color:var(--gold);font-weight:600;" id="hero-eth">ETH: $3,280</div>
      <div style="background:rgba(24,144,255,.08);border:1px solid rgba(24,144,255,.18);border-radius:var(--r);padding:8px 14px;font-size:.8rem;color:var(--blu);font-weight:600;" id="hero-bnb">BNB: $425</div>
    </div>
  </div>
</section>

<!-- STATS BAR -->
<div class="stats-bar">
  <div class="sb-item"><div class="sb-label">Active Members</div><div class="sb-val" style="color:var(--gold);"><?= number_format($totalMembers) ?>+</div></div>
  <div class="sb-item"><div class="sb-label">Total Volume</div><div class="sb-val" style="color:var(--grn);">$4.2M+</div></div>
  <div class="sb-item"><div class="sb-label">Profits Paid</div><div class="sb-val"><?= $totalPaid ?>+</div></div>
  <div class="sb-item"><div class="sb-label">Trade Success</div><div class="sb-val" style="color:var(--grn);">98.6%</div></div>
</div>

<!-- LIVE CHART -->
<div class="section" style="margin-bottom:28px;">
  <div class="panel card-gold">
    <div class="panel-hd">
      <div class="panel-title"><span class="panel-dot"></span>BTC/USDT — Live Chart</div>
      <div style="display:flex;align-items:center;gap:8px;">
        <span id="chart-price" style="font-family:var(--fm);font-size:1rem;font-weight:700;color:var(--grn);">$67,420</span>
        <span id="chart-chg" class="badge bg-g">▲ +2.4%</span>
      </div>
    </div>
    <div style="padding:12px 14px 16px;"><div class="chart-box"><canvas id="main-chart"></canvas></div></div>
  </div>
</div>

<!-- INVESTMENT PLANS -->
<div class="section" id="plans-section">
  <div class="section-head">
    <div class="section-tag">Investment Plans</div>
    <div class="section-h2">Choose Your Path to Growth</div>
    <div class="section-sub">Three professional investment tiers designed for every level of trader. All plans include copy trading access and referral bonuses.</div>
  </div>
  <div class="plans-grid">

    <!-- BASIC -->
    <div class="plan-card plan-basic">
      <div class="plan-icon">🔵</div>
      <div class="plan-name">Basic Plan</div>
      <div class="plan-price">$125 <span>deposit</span></div>
      <div class="plan-bonus">+ $12.50 Welcome Bonus</div>
      <div class="plan-div"></div>
      <ul class="plan-feat">
        <li>1% profit per trade session</li>
        <li>Copy trade access (Morning & Evening)</li>
        <li>+0.5% referral bonus per active member</li>
        <li>$50/month salary at 30 referrals</li>
        <li>USDT BEP20 / TRC20 withdrawal</li>
        <li>20% withdrawal fee</li>
      </ul>
      <button class="btn btn-out btn-full" style="margin-top:16px;border-color:rgba(96,165,250,.4);color:#60a5fa;" onclick="<?= $cu ? "location.href='/packages.php'" : "openMo('mo-register')" ?>">Activate Basic</button>
    </div>

    <!-- SILVER -->
    <div class="plan-card plan-silver">
      <div class="plan-icon">🥈</div>
      <div class="plan-name">Silver Plan</div>
      <div class="plan-price">$250 <span>deposit</span></div>
      <div class="plan-bonus">+ $25 Welcome Bonus</div>
      <div class="plan-div"></div>
      <ul class="plan-feat">
        <li>Competitive daily trade profit</li>
        <li>Copy trade access (Morning & Evening)</li>
        <li>+0.5% referral bonus per active member</li>
        <li>$150/month salary at 12 referrals</li>
        <li>Priority copy trade signals</li>
        <li>USDT BEP20 / TRC20 withdrawal</li>
      </ul>
      <button class="btn btn-out btn-full" style="margin-top:16px;" onclick="<?= $cu ? "location.href='/packages.php'" : "openMo('mo-register')" ?>">Activate Silver</button>
    </div>

    <!-- GOLD -->
    <div class="plan-card plan-gold plan-popular">
      <div class="plan-badge">MOST POPULAR</div>
      <div class="plan-icon">🥇</div>
      <div class="plan-name">Gold Plan</div>
      <div class="plan-price">$500 <span>deposit</span></div>
      <div class="plan-bonus">+ $50 Welcome Bonus</div>
      <div class="plan-div"></div>
      <ul class="plan-feat">
        <li>Highest daily trade profit</li>
        <li>Priority copy trade signals</li>
        <li>+0.5% referral bonus per active member</li>
        <li>$300/month salary at 6 referrals</li>
        <li>Exclusive Gold community access</li>
        <li>Priority withdrawal processing</li>
        <li>Eligible for all reward prizes</li>
      </ul>
      <button class="btn btn-gold btn-full" style="margin-top:16px;" onclick="<?= $cu ? "location.href='/packages.php'" : "openMo('mo-register')" ?>">Activate Gold</button>
    </div>
  </div>
</div>

<!-- REFERRAL REWARDS -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">🏆 Referral Rewards</div>
    <div class="section-h2">Refer & Win Life-Changing Prizes</div>
    <div class="section-sub">Build your network and unlock exclusive rewards. The more members you refer, the bigger the prize.</div>
  </div>

  <!-- Bike -->
  <div class="rew-banner" style="background:linear-gradient(135deg,#1a1000,#2a1800);border:1px solid rgba(240,185,11,.22);">
    <div class="rew-body">
      <div class="rew-icon">🏍️</div>
      <div class="rew-info">
        <div class="rew-label" style="color:var(--gold);">Reward #1 · 200 Referrals</div>
        <div class="rew-title">WIN A MOTORCYCLE</div>
        <div class="rew-desc">Refer 200 active members — brand new motorcycle delivered to you</div>
      </div>
    </div>
    <div class="rew-foot" style="background:rgba(240,185,11,.05);border-top:1px solid rgba(240,185,11,.15);">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:.66rem;color:var(--tx3);">Progress</span><span id="bike-txt" style="font-size:.7rem;color:var(--gold);font-weight:700;">0 / 200</span></div>
      <div class="prog-bar"><div class="prog-fill" id="bike-bar" style="width:0%;background:linear-gradient(90deg,var(--gold),#e0870a);"></div></div>
    </div>
  </div>

  <!-- Visa -->
  <div class="rew-banner" style="background:linear-gradient(135deg,#001020,#001a30);border:1px solid rgba(24,144,255,.2);">
    <div class="rew-body">
      <div class="rew-icon">✈️</div>
      <div class="rew-info">
        <div class="rew-label" style="color:var(--blu);">Reward #2 · 400 Referrals</div>
        <div class="rew-title">EUROPE TRIP VISA</div>
        <div class="rew-desc">Refer 400 active members — full Europe travel visa & trip sponsorship</div>
      </div>
    </div>
    <div class="rew-foot" style="background:rgba(24,144,255,.04);border-top:1px solid rgba(24,144,255,.15);">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:.66rem;color:var(--tx3);">Progress</span><span id="visa-txt" style="font-size:.7rem;color:var(--blu);font-weight:700;">0 / 400</span></div>
      <div class="prog-bar"><div class="prog-fill" id="visa-bar" style="width:0%;background:linear-gradient(90deg,var(--blu),#60a5fa);"></div></div>
    </div>
  </div>

  <!-- Car -->
  <div class="rew-banner" style="background:linear-gradient(135deg,#0a0018,#140028);border:1px solid rgba(139,92,246,.2);margin-bottom:0;">
    <div class="rew-body">
      <div class="rew-icon">🚗</div>
      <div class="rew-info">
        <div class="rew-label" style="color:var(--pur);">Reward #3 · 600 Referrals</div>
        <div class="rew-title">CAR 1000CC+</div>
        <div class="rew-desc">Refer 600 active members — your dream car delivered to your door</div>
      </div>
    </div>
    <div class="rew-foot" style="background:rgba(139,92,246,.04);border-top:1px solid rgba(139,92,246,.18);">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span style="font-size:.66rem;color:var(--tx3);">Progress</span><span id="car-txt" style="font-size:.7rem;color:var(--pur);font-weight:700;">0 / 600</span></div>
      <div class="prog-bar"><div class="prog-fill" id="car-bar" style="width:0%;background:linear-gradient(90deg,var(--pur),#6d28d9);"></div></div>
    </div>
  </div>
</div>

<!-- MONTHLY SALARY -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">💰 Monthly Salary</div>
    <div class="section-h2">Earn a Monthly Salary</div>
    <div class="section-sub">Achieve referral milestones and earn a guaranteed monthly salary on top of your trading profits.</div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
    <div class="card" style="padding:16px;text-align:center;border-color:rgba(96,165,250,.2);">
      <div style="font-size:1.6rem;margin-bottom:8px;">🔵</div>
      <div style="font-size:.65rem;color:var(--tx3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Basic Plan</div>
      <div style="font-family:var(--fh);font-size:1.1rem;font-weight:700;color:#60a5fa;">$50/month</div>
      <div style="font-size:.72rem;color:var(--tx3);margin-top:5px;">Refer 30 Basic members</div>
    </div>
    <div class="card" style="padding:16px;text-align:center;border-color:rgba(192,192,192,.2);">
      <div style="font-size:1.6rem;margin-bottom:8px;">🥈</div>
      <div style="font-size:.65rem;color:var(--tx3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Silver Plan</div>
      <div style="font-family:var(--fh);font-size:1.1rem;font-weight:700;color:#c0c0c0;">$150/month</div>
      <div style="font-size:.72rem;color:var(--tx3);margin-top:5px;">Refer 12 Silver members</div>
    </div>
    <div class="card card-gold" style="padding:16px;text-align:center;">
      <div style="font-size:1.6rem;margin-bottom:8px;">🥇</div>
      <div style="font-size:.65rem;color:var(--tx3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Gold Plan</div>
      <div style="font-family:var(--fh);font-size:1.1rem;font-weight:700;color:var(--gold);">$300/month</div>
      <div style="font-size:.72rem;color:var(--tx3);margin-top:5px;">Refer 6 Gold members</div>
    </div>
  </div>
</div>

<!-- HOW IT WORKS -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">How It Works</div>
    <div class="section-h2">Start Earning in 4 Simple Steps</div>
  </div>
  <div style="display:flex;flex-direction:column;gap:10px;">
    <div class="step-card"><div class="step-num">01</div><div><div class="step-title">Register & Select Plan</div><div class="step-desc">Create your free account and choose Basic ($125), Silver ($250), or Gold ($500).</div></div></div>
    <div class="step-card"><div class="step-num">02</div><div><div class="step-title">Deposit via Binance</div><div class="step-desc">Send USDT (BEP20 or TRC20) to our wallet. Admin confirms your balance within 1 hour.</div></div></div>
    <div class="step-card"><div class="step-num">03</div><div><div class="step-title">Copy Admin Trade Signals</div><div class="step-desc">Receive signals in the Broadcast channel. Trade 2× daily in the 8–9 AM & 6–7 PM windows.</div></div></div>
    <div class="step-card"><div class="step-num">04</div><div><div class="step-title">Earn, Refer & Withdraw</div><div class="step-desc">Collect daily trade profits + referral bonuses. Withdraw anytime (20% processing fee).</div></div></div>
  </div>
</div>

<!-- DEPOSIT INFO -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">Deposit System</div>
    <div class="section-h2">Binance USDT Deposit</div>
    <div class="section-sub">We accept USDT on BEP20 (BSC) and TRC20 networks. All transactions are manually verified by our admin team.</div>
  </div>
  <div class="card card-gold" style="padding:18px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
      <div>
        <div style="font-size:.66rem;color:var(--tx3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.8px;">BEP20 (BSC) Wallet</div>
        <div class="copy-row"><span class="copy-val"><?= htmlspecialchars(setting('wallet_bep20','0x742d35...')) ?></span><button class="btn btn-xs btn-gold" onclick="copyText('<?= htmlspecialchars(setting('wallet_bep20')) ?>')">Copy</button></div>
      </div>
      <div>
        <div style="font-size:.66rem;color:var(--tx3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.8px;">TRC20 Wallet</div>
        <div class="copy-row"><span class="copy-val"><?= htmlspecialchars(setting('wallet_trc20','TJYe...')) ?></span><button class="btn btn-xs btn-gold" onclick="copyText('<?= htmlspecialchars(setting('wallet_trc20')) ?>')">Copy</button></div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
      <div style="background:var(--bg3);border-radius:var(--r);padding:9px;text-align:center;"><div style="font-size:.58rem;color:var(--tx3);">TOKENS</div><div style="font-weight:700;color:var(--gold);margin-top:2px;">USDT / USDC</div></div>
      <div style="background:var(--bg3);border-radius:var(--r);padding:9px;text-align:center;"><div style="font-size:.58rem;color:var(--tx3);">NETWORKS</div><div style="font-weight:700;color:var(--gold);margin-top:2px;">BEP20 / TRC20</div></div>
      <div style="background:var(--bg3);border-radius:var(--r);padding:9px;text-align:center;"><div style="font-size:.58rem;color:var(--tx3);">CONFIRM TIME</div><div style="font-weight:700;color:var(--grn);margin-top:2px;">~1 Hour</div></div>
    </div>
    <div style="font-size:.72rem;color:var(--tx3);margin-top:12px;text-align:center;"><span style="color:var(--gold);">⏱</span> After sending, submit your TX hash in the dashboard. Admin approves within 1 hour.</div>
  </div>
</div>

<!-- WITHDRAWAL INFO -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">Withdrawal System</div>
    <div class="section-h2">Fast & Secure Withdrawals</div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;">
    <div class="stat-box sb-grn"><div class="sb-lbl">Processing Time</div><div class="sb-val grn">24 Hours</div><div class="sb-sub">After admin approval</div></div>
    <div class="stat-box sb-red"><div class="sb-lbl">Withdrawal Fee</div><div class="sb-val red"><?= setting('withdraw_fee','20') ?>%</div><div class="sb-sub">Deducted from amount</div></div>
    <div class="stat-box sb-blu"><div class="sb-lbl">Min Withdrawal</div><div class="sb-val blu">$10</div><div class="sb-sub">USDT equivalent</div></div>
    <div class="stat-box sb-gold"><div class="sb-lbl">Supported</div><div class="sb-val gold">USDT</div><div class="sb-sub">BEP20 / TRC20</div></div>
  </div>
</div>

<!-- TRADE WINDOWS -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">Trade Windows</div>
    <div class="section-h2">Daily Trading Schedule</div>
    <div class="section-sub">Copy trade signals are sent twice daily. You have exactly 1 hour to execute each trade. Missing the window means losing that session's profit.</div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    <div class="card" style="padding:18px;text-align:center;border-color:rgba(240,185,11,.2);">
      <div style="font-size:2rem;margin-bottom:8px;">🌅</div>
      <div style="font-size:.68rem;color:var(--tx3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Morning Session</div>
      <div style="font-family:var(--fh);font-size:1.1rem;font-weight:700;color:var(--gold);">8:00 – 9:00 AM</div>
      <div style="font-size:.74rem;color:var(--tx3);margin-top:6px;">Code sent at 8:00 AM<br>Expires at 9:00 AM sharp</div>
    </div>
    <div class="card" style="padding:18px;text-align:center;border-color:rgba(139,92,246,.2);">
      <div style="font-size:2rem;margin-bottom:8px;">🌆</div>
      <div style="font-size:.68rem;color:var(--tx3);margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">Evening Session</div>
      <div style="font-family:var(--fh);font-size:1.1rem;font-weight:700;color:var(--pur);">6:00 – 7:00 PM</div>
      <div style="font-size:.74rem;color:var(--tx3);margin-top:6px;">Code sent at 6:00 PM<br>Expires at 7:00 PM sharp</div>
    </div>
  </div>
</div>

<!-- REFERRAL SYSTEM -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">Referral System</div>
    <div class="section-h2">Grow Your Earnings with Referrals</div>
    <div class="section-sub">Every active referral increases your earning potential. Earn a percentage of your referrals' deposits automatically.</div>
  </div>
  <div style="display:flex;flex-direction:column;gap:8px;">
    <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:14px;">
      <div style="width:40px;height:40px;border-radius:var(--r);background:var(--blud);border:1px solid rgba(96,165,250,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">🔵</div>
      <div style="flex:1;"><div style="font-weight:700;font-size:.9rem;">Basic Plan Referrals</div><div style="font-size:.76rem;color:var(--tx3);margin-top:2px;">Earn 0.5% of each referral's deposit per trade</div></div>
      <div style="text-align:right;"><div style="font-family:var(--fh);font-size:.95rem;font-weight:700;color:#60a5fa;">0.5%</div><div style="font-size:.66rem;color:var(--tx3);">per trade</div></div>
    </div>
    <div class="card" style="padding:14px 16px;display:flex;align-items:center;gap:14px;">
      <div style="width:40px;height:40px;border-radius:var(--r);background:rgba(192,192,192,.08);border:1px solid rgba(192,192,192,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">🥈</div>
      <div style="flex:1;"><div style="font-weight:700;font-size:.9rem;">Silver Plan Referrals</div><div style="font-size:.76rem;color:var(--tx3);margin-top:2px;">Earn 0.5% of each referral's deposit per trade</div></div>
      <div style="text-align:right;"><div style="font-family:var(--fh);font-size:.95rem;font-weight:700;color:#c0c0c0;">0.5%</div><div style="font-size:.66rem;color:var(--tx3);">per trade</div></div>
    </div>
    <div class="card card-gold" style="padding:14px 16px;display:flex;align-items:center;gap:14px;">
      <div style="width:40px;height:40px;border-radius:var(--r);background:var(--gd);border:1px solid rgba(240,185,11,.2);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">🥇</div>
      <div style="flex:1;"><div style="font-weight:700;font-size:.9rem;">Gold Plan Referrals</div><div style="font-size:.76rem;color:var(--tx3);margin-top:2px;">Earn 0.5% of each referral's deposit per trade</div></div>
      <div style="text-align:right;"><div style="font-family:var(--fh);font-size:.95rem;font-weight:700;color:var(--gold);">0.5%</div><div style="font-size:.66rem;color:var(--tx3);">per trade</div></div>
    </div>
  </div>
</div>

<!-- FAQ -->
<div class="section">
  <div class="section-head">
    <div class="section-tag">FAQ</div>
    <div class="section-h2">Frequently Asked Questions</div>
  </div>
  <div id="faq-list">
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>What is copy trading?</span><span class="arrow">▼</span></div><div class="faq-a">Copy trading allows you to automatically replicate the trades of expert traders. When our admin sends a trade signal (code), you enter it in the Trade page and your position follows the same market move, earning you a percentage profit.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>How do I deposit funds?</span><span class="arrow">▼</span></div><div class="faq-a">Send USDT (BEP20 or TRC20) to our Binance wallet address. Then go to your dashboard, submit your TX hash as proof, and our admin will approve it within 1 hour. Your balance will update automatically.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>When can I trade?</span><span class="arrow">▼</span></div><div class="faq-a">You can trade twice daily. Morning session: 8:00–9:00 AM. Evening session: 6:00–7:00 PM. Admin sends the copy trade code at the start of each window. You must execute within the 1-hour window or you'll miss that session.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>How much can I earn per trade?</span><span class="arrow">▼</span></div><div class="faq-a">Profit depends on your plan and the admin's signal. Basic plan earns 1% per trade on your deposit. Silver and Gold plans earn according to the signal's set percentage. Referral bonuses (0.5% per referral) are added on top.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>How do referral bonuses work?</span><span class="arrow">▼</span></div><div class="faq-a">When someone registers using your referral link and deposits, you earn 0.5% of their deposit amount each time they complete a trade. This scales with more referrals. Reach milestones for monthly salary bonuses and exclusive rewards.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>How do I withdraw my earnings?</span><span class="arrow">▼</span></div><div class="faq-a">Go to your Dashboard → Withdraw. Enter the amount, your Binance wallet address, and select BEP20 or TRC20. A 20% processing fee is deducted. Admin processes withdrawals within 24 hours.</div></div>
    <div class="faq-item"><div class="faq-q" onclick="toggleFaq(this)"><span>Is my investment safe?</span><span class="arrow">▼</span></div><div class="faq-a">All trading involves risk. WorkupX provides professional signals and infrastructure, but market conditions can affect returns. We strongly advise only investing amounts you can afford to lose. Read our full Risk Disclosure before investing.</div></div>
  </div>
</div>


<!-- CTA -->
<div class="section" style="margin-bottom:0;">
  <div class="card card-gold" style="padding:28px 20px;text-align:center;background:linear-gradient(135deg,rgba(240,185,11,.08),rgba(240,185,11,.03));">
    <div class="nav-logo" style="justify-content:center;margin-bottom:14px;"><img src="logo.png" alt="WorkupX" style="height:40px;"></div>
    <div style="font-family:var(--fh);font-size:1.4rem;font-weight:800;margin-bottom:8px;">Ready to Start Earning?</div>
    <div style="font-size:.84rem;color:var(--tx3);margin-bottom:20px;">Join <?= number_format($totalMembers) ?>+ traders already earning daily with WorkupX</div>
    <?php if(!$cu): ?>
    <button class="btn btn-gold" onclick="openMo('mo-register')" style="font-size:1rem;padding:14px 32px;">🚀 Create Free Account</button>
    <?php else: ?>
    <a href="/dashboard.php" class="btn btn-gold" style="font-size:1rem;padding:14px 32px;">📊 Go to Dashboard →</a>
    <?php endif; ?>
  </div>
</div>

</main>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-logo"><img src="logo.png" alt="WorkupX"></div>
  <div class="footer-desc">WorkupX is a professional copy trading and investment platform. We provide expert trade signals, a structured referral network, and transparent earnings management.</div>
  <div class="footer-links">
    <a href="/terms.php">Terms & Conditions</a>
    <a href="/risk.php">Risk Disclosure</a>
    <a href="/privacy.php">Privacy Policy</a>
    <a href="/aml.php">AML/KYC Policy</a>
    <a href="/login.php">Login</a>
    <?php if(!$cu): ?><a onclick="openMo('mo-register')" style="cursor:pointer;color:var(--gold);">Register</a><?php endif; ?>
  </div>
  <div class="footer-copy">© <?= date('Y') ?> WorkupX.com — All rights reserved. Trading involves risk. Not financial advice.</div>
</footer>

<!-- BOTTOM NAV (logged in mobile) -->
<?php if($cu): ?>
<nav class="bnav" style="display:flex;">
  <a class="nav-i active" href="/"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Home</a>
  <a class="nav-i" href="/dashboard.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Dashboard</a>
  <a class="nav-i" href="/trade.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>Trade</a>
  <a class="nav-i" href="/assets.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>Assets</a>
  <a class="nav-i" href="/referral.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>Refer</a>
  <a class="nav-i" href="/signals.php" style="position:relative;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.69 12 19.79 19.79 0 011.61 3.5 2 2 0 013.6 1.32h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.91 8a16 16 0 006 6l.91-.91a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0121.74 16z"/></svg>Signals</a>
</nav>
<?php endif; ?>

<script>
/* ─── PRICES ─────────────────────────────────────────────────── */
let P={BTC:67420,ETH:3280,BNB:425,SOL:182,XRP:.62,DOGE:.14,ADA:.58,MATIC:.95,LINK:18.4,DOT:8.2,AVAX:42.1,LTC:96.5};
let C={BTC:2.4,ETH:-1.2,BNB:3.1,SOL:5.8,XRP:-.8,DOGE:8.2,ADA:1.5,MATIC:-2.1,LINK:4.3,DOT:-.6,AVAX:3.7,LTC:1.1};
let CI={};

function fp(p){if(!p)return'$0';if(p>=1000)return'$'+p.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});if(p>=1)return'$'+p.toFixed(2);return'$'+p.toFixed(5);}

function simPrices(){
  Object.keys(P).forEach(c=>{
    P[c]=+(P[c]*(1+(Math.random()-.485)*.0025)).toFixed(P[c]>10?2:6);
    C[c]=+(C[c]+(Math.random()-.5)*.05).toFixed(2);
  });
  // Update hero prices
  const hb=document.getElementById('hero-btc');if(hb)hb.textContent='BTC: '+fp(P.BTC);
  const he=document.getElementById('hero-eth');if(he)he.textContent='ETH: '+fp(P.ETH);
  const hn=document.getElementById('hero-bnb');if(hn)hn.textContent='BNB: '+fp(P.BNB);
  // Update chart price
  const cp=document.getElementById('chart-price');if(cp)cp.textContent=fp(P.BTC);
  const cc=document.getElementById('chart-chg');
  if(cc){const up=C.BTC>=0;cc.textContent=(up?'▲ +':'▼ ')+C.BTC+'%';cc.className='badge '+(up?'bg-g':'bg-r');}
  // Push to main chart
  pushChart('main-chart',P.BTC);
  // Ticker
  renderTicker();
}

function renderTicker(){
  const keys=Object.keys(P);const el=document.getElementById('ticker-inner');if(!el)return;
  el.innerHTML=[...keys,...keys].map(c=>`<span class="tick-i"><span class="s">${c}/USDT</span><span class="${C[c]>=0?'up':'dn'}">${fp(P[c])} (${C[c]>=0?'+':''}${C[c]}%)</span></span>`).join('');
}

function randWalk(base,n=70,v=.014){const d=[];let p=base;for(let i=0;i<n;i++){p+=p*(Math.random()-.48)*v;d.push(+p.toFixed(4));}return d;}

function mkChart(id,data,col='#f0b90b'){
  const cv=document.getElementById(id);if(!cv)return;
  if(CI[id]){try{CI[id].destroy();}catch(e){}}
  const ctx=cv.getContext('2d');const h=cv.offsetHeight||200;
  const gr=ctx.createLinearGradient(0,0,0,h);gr.addColorStop(0,col+'40');gr.addColorStop(1,col+'00');
  CI[id]=new Chart(ctx,{type:'line',data:{labels:Array(data.length).fill(''),datasets:[{data,borderColor:col,borderWidth:2,backgroundColor:gr,pointRadius:0,tension:.4,fill:true}]},
    options:{responsive:true,maintainAspectRatio:false,animation:{duration:200},plugins:{legend:{display:false},tooltip:{backgroundColor:'rgba(7,11,18,.95)',borderColor:col,borderWidth:1,titleColor:col,bodyColor:'#eaecef',padding:10,callbacks:{label:c=>' '+fp(c.parsed.y)}}},scales:{x:{display:false},y:{display:false}}}});
}

function pushChart(id,val){const ch=CI[id];if(!ch)return;const ds=ch.data.datasets[0];ds.data.push(val);if(ds.data.length>80)ds.data.shift();try{ch.update('none');}catch(e){}}

/* ─── INIT ───────────────────────────────────────────────────── */
window.addEventListener('load',()=>{
  setTimeout(()=>mkChart('main-chart',randWalk(67420),'#f0b90b'),200);
  renderTicker();
  setInterval(simPrices,2200);
});

/* ─── MODALS ─────────────────────────────────────────────────── */
function openMo(id){document.getElementById(id)?.classList.add('open');}
function closeMo(id){document.getElementById(id)?.classList.remove('open');}
document.querySelectorAll('.mo').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));

/* ─── TOAST ──────────────────────────────────────────────────── */
function toast(msg,type='success'){
  const w=document.getElementById('toast-wrap');
  const d=document.createElement('div');d.className='toast '+type;
  d.innerHTML=`<span>${{success:'✅',error:'❌',info:'ℹ️'}[type]||'ℹ️'}</span><span style="flex:1;">${msg}</span>`;
  w.appendChild(d);setTimeout(()=>{d.style.opacity='0';d.style.transform='translateX(14px)';d.style.transition='all .3s';setTimeout(()=>d.remove(),300);},3500);
}

/* ─── COPY ───────────────────────────────────────────────────── */
function copyText(t){navigator.clipboard?.writeText(t).then(()=>toast('Copied!','success')).catch(()=>toast('Copy failed','error'));}

/* ─── FAQ ────────────────────────────────────────────────────── */
function toggleFaq(el){const a=el.nextElementSibling;el.classList.toggle('open');a.classList.toggle('open');}

/* ─── LOGIN FORM ─────────────────────────────────────────────── */
document.getElementById('login-form')?.addEventListener('submit',async e=>{
  e.preventDefault();const btn=document.getElementById('login-btn');btn.disabled=true;btn.textContent='Signing in...';
  const fd=new FormData(e.target);
  const r=await fetch('/api/user/login.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(x=>x.json()).catch(()=>({ok:false,msg:'Network error'}));
  const el=document.getElementById('login-msg');el.style.color=r.ok?'var(--grn)':'var(--red)';el.textContent=r.msg;
  if(r.ok){toast('Welcome back! Redirecting...','success');setTimeout(()=>location.href='/dashboard.php',900);}
  else{btn.disabled=false;btn.textContent='Sign In →';}
});

/* ─── REGISTER FORM ──────────────────────────────────────────── */
document.getElementById('reg-form')?.addEventListener('submit',async e=>{
  e.preventDefault();const btn=document.getElementById('reg-btn');btn.disabled=true;btn.textContent='Creating account...';
  const fd=new FormData(e.target);
  if(fd.get('password')!==fd.get('password2')){
    document.getElementById('reg-msg').style.color='var(--red)';
    document.getElementById('reg-msg').textContent='Passwords do not match.';
    btn.disabled=false;btn.textContent='Create My Account →';return;
  }
  const r=await fetch('/api/user/register.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(x=>x.json()).catch(()=>({ok:false,msg:'Network error'}));
  const el=document.getElementById('reg-msg');el.style.color=r.ok?'var(--grn)':'var(--red)';el.textContent=r.msg;
  if(r.ok){toast('Account created! Redirecting...','success');setTimeout(()=>location.href='/dashboard.php',1000);}
  else{btn.disabled=false;btn.textContent='Create My Account →';}
});

/* ─── FORGOT PASSWORD ────────────────────────────────────────── */
document.getElementById('forgot-form1')?.addEventListener('submit',async e=>{
  e.preventDefault();const fd=new FormData(e.target);
  const r=await fetch('/api/user/forgot.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(x=>x.json()).catch(()=>({ok:false,msg:'Error'}));
  const el=document.getElementById('forgot-msg1');el.style.color=r.ok?'var(--grn)':'var(--red)';el.textContent=r.msg;
  if(r.ok){document.getElementById('forgot-step1').style.display='none';document.getElementById('forgot-step2').style.display='';document.getElementById('otp-display').textContent=r.otp||'——';}
});
document.getElementById('forgot-form2')?.addEventListener('submit',async e=>{
  e.preventDefault();const fd=new FormData(e.target);
  const r=await fetch('/api/user/reset_pass.php',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(x=>x.json()).catch(()=>({ok:false,msg:'Error'}));
  const el=document.getElementById('forgot-msg2');el.style.color=r.ok?'var(--grn)':'var(--red)';el.textContent=r.msg;
  if(r.ok){toast('Password reset! Please login.','success');setTimeout(()=>{closeMo('mo-forgot');openMo('mo-login');},1200);}
});
</script>
</body>
</html>