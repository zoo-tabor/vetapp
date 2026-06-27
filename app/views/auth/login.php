<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení - VetApp ZOO Tábor</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body.login-page.zt { background: #0c0f0c; }
        #ztScene { position: fixed; inset: 0; z-index: 0; display: block; }
        .zt-vignette { position: fixed; inset: 0; z-index: 1; pointer-events: none;
            background: radial-gradient(120% 90% at 50% 45%, transparent 40%, rgba(6,10,6,.5) 80%, rgba(4,7,4,.92) 100%); }
        .zt .login-container { position: relative; z-index: 3; pointer-events: none; }
        .zt .login-box { pointer-events: auto; box-shadow: 0 16px 40px rgba(0,0,0,0.45); }
        /* Box is hidden only while the intro animation is actually running (JS adds zt-anim).
           Without JS / on fallback the box stays visible by default. */
        body.zt-anim .login-box { opacity: 0; transform: translateY(12px); transition: opacity .9s ease, transform .9s ease; }
        body.zt-anim.zt-reveal .login-box { opacity: 1; transform: none; }
        @media (max-width: 760px) { #ztScene { display: none; } .zt .login-container { pointer-events: auto; } }
    </style>
</head>
<body class="login-page zt">
    <canvas id="ztScene"></canvas>
    <div class="zt-vignette"></div>

    <div class="login-container">
        <div class="login-box">
            <h1>VetApp</h1>
            <p class="login-subtitle">Přihlaste se pro pokračování</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="login-form">
                <div class="form-group">
                    <label for="username">Uživatelské jméno</label>
                    <input type="text" id="username" name="username" required autofocus class="form-control">
                </div>

                <div class="form-group">
                    <label for="password">Heslo</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Přihlásit se
                </button>
            </form>

            <?php if (isset($error)): ?>
                <p class="login-link">
                    <a href="/forgot-password">Zapomněli jste heslo?</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    (function () {
        var canvas = document.getElementById('ztScene');
        function revealBox() { document.body.classList.add('zt-reveal'); }

        var small = window.matchMedia('(max-width: 760px)').matches;
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (small || reduced || !canvas || !canvas.getContext) { return; } // fallback: box stays visible

        document.body.classList.add('zt-anim');
        var safety = setTimeout(revealBox, 8000);
        try { runIntro(); } catch (e) { clearTimeout(safety); revealBox(); }

        function runIntro() {
            var ctx = canvas.getContext('2d');
            var DPR = Math.min(window.devicePixelRatio || 1, 2);
            var W = 0, H = 0, cx = 0, cy = 0, minDim = 0;

            function glow(rgb) {
                var s = 64, c = document.createElement('canvas'); c.width = c.height = s;
                var g = c.getContext('2d');
                var r = g.createRadialGradient(s/2,s/2,0,s/2,s/2,s/2);
                r.addColorStop(0,'rgba('+rgb+',1)'); r.addColorStop(.25,'rgba('+rgb+',.6)');
                r.addColorStop(.6,'rgba('+rgb+',.16)'); r.addColorStop(1,'rgba('+rgb+',0)');
                g.fillStyle = r; g.fillRect(0,0,s,s); return c;
            }
            var gNet = glow('96,142,82'), gName = glow('255,248,235'), gAccent = glow('230,126,34'), gSignal = glow('245,184,96');
            function rnd(a,b){ return a + Math.random()*(b-a); }
            function clamp(v,a,b){ return v<a?a:(v>b?b:v); }
            function easeOut(t){ return 1-Math.pow(1-t,3); }
            function easeIO(t){ return t<0.5?4*t*t*t:1-Math.pow(-2*t+2,3)/2; }

            // ---- vector animal silhouettes (font independent) ----
            function ear(g,a,b,c,d,e,f){ g.beginPath(); g.moveTo(a,b); g.lineTo(c,d); g.lineTo(e,f); g.closePath(); g.fill(); }
            function el(g,x,y,rx,ry){ g.beginPath(); g.ellipse(x,y,rx,ry,0,0,6.2832); g.fill(); }
            var SIL = [
                function cat(g){ el(g,50,64,22,24); el(g,50,50,12,21); el(g,50,30,15,13);
                    ear(g,38,22,34,5,47,18); ear(g,62,22,66,5,53,18);
                    g.beginPath(); g.moveTo(70,72); g.quadraticCurveTo(92,64,84,42); g.lineTo(78,47); g.quadraticCurveTo(82,62,66,68); g.closePath(); g.fill(); },
                function owl(g){ el(g,50,57,25,33); ear(g,34,30,42,7,50,28); ear(g,66,30,58,7,50,28); },
                function turtle(g){ el(g,50,52,31,22); el(g,50,24,9,9); el(g,24,70,8,8); el(g,76,70,8,8); el(g,28,36,8,7); el(g,72,36,8,7); el(g,50,76,6,7); },
                function peacock(g){ g.beginPath(); g.moveTo(46,64); g.arc(46,64,40,Math.PI*1.06,Math.PI*1.94,false); g.closePath(); g.fill();
                    el(g,41,68,11,16); g.beginPath(); g.moveTo(36,57); g.lineTo(30,35); g.lineTo(37,35); g.lineTo(43,57); g.closePath(); g.fill(); el(g,31,32,6,6); },
                function stag(g){ el(g,46,64,24,13); g.fillRect(30,68,4,16); g.fillRect(42,69,4,15); g.fillRect(54,69,4,15); g.fillRect(64,68,4,16);
                    g.beginPath(); g.moveTo(62,60); g.lineTo(70,38); g.lineTo(77,38); g.lineTo(70,60); g.closePath(); g.fill(); el(g,74,33,7,6);
                    g.lineWidth=3; g.lineCap='round';
                    g.beginPath(); g.moveTo(71,30); g.lineTo(64,14); g.moveTo(71,30); g.lineTo(80,14); g.moveTo(67,22); g.lineTo(60,18); g.moveTo(76,21); g.lineTo(84,18); g.stroke(); },
                function camel(g){ el(g,50,58,26,12); el(g,42,48,9,9); el(g,58,48,9,9);
                    g.beginPath(); g.moveTo(70,56); g.lineTo(80,34); g.lineTo(86,37); g.lineTo(76,58); g.closePath(); g.fill(); el(g,83,32,6,6);
                    g.fillRect(34,66,4,18); g.fillRect(44,66,4,18); g.fillRect(56,66,4,18); g.fillRect(64,66,4,18); },
                function kangaroo(g){ el(g,44,60,18,22); el(g,54,40,12,14); el(g,61,24,8,8);
                    g.beginPath(); g.moveTo(30,66); g.quadraticCurveTo(10,82,30,88); g.lineTo(42,74); g.closePath(); g.fill(); g.fillRect(40,82,24,6); },
                function parrot(g){ el(g,52,52,15,21); el(g,52,28,12,12); ear(g,42,26,30,30,42,34);
                    g.beginPath(); g.moveTo(49,70); g.lineTo(43,95); g.lineTo(57,95); g.lineTo(61,70); g.closePath(); g.fill(); }
            ];
            function sampleSil(drawer, cap){
                var S=130, c=document.createElement('canvas'); c.width=c.height=S;
                var g=c.getContext('2d'); g.clearRect(0,0,S,S);
                g.save(); g.scale(S/100,S/100); g.fillStyle='#fff'; g.strokeStyle='#fff'; g.lineJoin='round'; g.lineWidth=3;
                try{ drawer(g); }catch(e){} g.restore();
                var d=g.getImageData(0,0,S,S).data, pts=[], mnX=1e9,mxX=-1e9,mnY=1e9,mxY=-1e9;
                for(var y=0;y<S;y+=3)for(var x=0;x<S;x+=3){ if(d[(y*S+x)*4+3]>90){ pts.push([x,y]);
                    if(x<mnX)mnX=x; if(x>mxX)mxX=x; if(y<mnY)mnY=y; if(y>mxY)mxY=y; } }
                for(var s=pts.length-1;s>0;s--){ var j=(Math.random()*(s+1))|0; var t=pts[s]; pts[s]=pts[j]; pts[j]=t; }
                if(pts.length>cap) pts.length=cap;
                var w=Math.max(mxX-mnX,1), h=Math.max(mxY-mnY,1), m=Math.max(w,h), ctX=(mnX+mxX)/2, ctY=(mnY+mxY)/2;
                return pts.map(function(p){ return [(p[0]-ctX)/m, (p[1]-ctY)/m]; });
            }
            var silCache = [];
            var bgAnimals = [], animalTimer = 2.0, MAX_ANIMALS = 2, animalSide = (Math.random()<0.5)?0:1;
            function spawnAnimal(){
                var idx = (Math.random()*SIL.length)|0;
                var pts = silCache[idx] || (silCache[idx] = sampleSil(SIL[idx], 150));
                var scale = rnd(0.26,0.40)*minDim;
                // Spawn only in the side margins (left/right), alternating, so the centered
                // login box never hides them. Gentle drift keeps them on their side.
                var left = (animalSide++ % 2 === 0);
                var px = left ? rnd(0.07,0.25)*W : rnd(0.75,0.93)*W, py = rnd(0.20,0.80)*H;
                var parts = pts.map(function(p){ return { nx:p[0], ny:p[1], x:px+rnd(-1,1)*scale, y:py+rnd(-1,1)*scale,
                    vx:0, vy:0, tw:rnd(0,6.28), twS:rnd(1,2.4), accent:Math.random()<0.18 }; });
                bgAnimals.push({ parts:parts, px:px, py:py, scale:scale, vx:rnd(-4,4), vy:rnd(-7,7),
                    life:0, maxLife:rnd(9,13), col:Math.random()<0.5?'g':'o' });
            }

            // ---- main "ZOO TÁBOR" network ----
            var nodes=[], recruitedIdx=[], contentW=900, SPREAD=0.92;
            function build(){
                nodes=[]; recruitedIdx=[];
                var tw=1900, th=400, oc=document.createElement('canvas'); oc.width=tw; oc.height=th;
                var g=oc.getContext('2d'); g.clearRect(0,0,tw,th);
                g.fillStyle='#fff'; g.textAlign='center'; g.textBaseline='middle';
                g.font='600 196px "Helvetica Neue",Arial,sans-serif';
                g.fillText('ZOO TÁBOR', tw/2, th/2+4);
                var d=g.getImageData(0,0,tw,th).data, step=3, pts=[], mnX=1e9,mxX=-1e9,mnY=1e9,mxY=-1e9;
                for(var y=0;y<th;y+=step)for(var x=0;x<tw;x+=step){ if(d[(y*tw+x)*4+3]>130){ pts.push([x,y]);
                    if(x<mnX)mnX=x; if(x>mxX)mxX=x; if(y<mnY)mnY=y; if(y>mxY)mxY=y; } }
                for(var s=pts.length-1;s>0;s--){ var j=(Math.random()*(s+1))|0; var t=pts[s]; pts[s]=pts[j]; pts[j]=t; }
                var CAP=1100; if(pts.length>CAP) pts.length=CAP;
                var ctX=(mnX+mxX)/2, ctY=(mnY+mxY)/2; contentW=(mxX-mnX);
                function field(n){ n.fnx=rnd(-1,1)*SPREAD; n.fny=rnd(-1,1)*SPREAD*0.66; n.fz=rnd(0.6,3.2);
                    n.da=rnd(0,6.28); n.ds=rnd(0.2,0.6); n.tw=rnd(0,6.28); n.twS=rnd(1.2,2.6); n._nbr=new Int32Array(6); n._nc=0; }
                for(var m=0;m<pts.length;m++){ var n={ recruited:true, tx:pts[m][0]-ctX, ty:pts[m][1]-ctY,
                    t0:2.05+rnd(0,0.85), size:rnd(0.9,1.6), assembled:0, accent:Math.random()<0.16, x:0,y:0,vx:0,vy:0 };
                    field(n); recruitedIdx.push(nodes.length); nodes.push(n); }
                for(var k=0;k<260;k++){ var a={ recruited:false, tx:0,ty:0, t0:0, size:rnd(1.0,2.0), assembled:0,
                    accent:Math.random()<0.10, x:0,y:0,vx:0,vy:0 }; field(a); nodes.push(a); }
            }

            var signals=[], MAXSIG=60;
            function pickStart(){ if(elapsed>PEAK-0.5 && recruitedIdx.length && Math.random()<0.62) return recruitedIdx[(Math.random()*recruitedIdx.length)|0]; return (Math.random()*nodes.length)|0; }
            function relocate(sig){ sig.ai=pickStart(); sig.bi=-1; sig.t=0; }
            function initSignals(){ signals=[]; for(var i=0;i<MAXSIG;i++){ var sig={ ai:0, bi:-1, t:Math.random(), speed:rnd(0.7,1.5), accent:Math.random()<0.3 }; relocate(sig); signals.push(sig); } }

            var mx=-9999, my=-9999, hovering=false, pSpeed=0, lx=0, ly=0;
            function setP(px,py){ pSpeed=Math.min(pSpeed+Math.hypot(px-lx,py-ly)*0.05,3); lx=px; ly=py; mx=px; my=py; hovering=true; }
            window.addEventListener('pointermove', function(e){ setP(e.clientX,e.clientY); }, {passive:true});
            window.addEventListener('pointerdown', function(e){ setP(e.clientX,e.clientY); }, {passive:true});
            window.addEventListener('blur', function(){ hovering=false; });

            var scaleLayout=1;
            function resize(){ W=window.innerWidth; H=window.innerHeight; canvas.width=W*DPR; canvas.height=H*DPR;
                canvas.style.width=W+'px'; canvas.style.height=H+'px'; ctx.setTransform(DPR,0,0,DPR,0,0);
                cx=W/2; cy=H/2; minDim=Math.min(W,H); scaleLayout=Math.min(W*0.92,1280)/contentW; }
            window.addEventListener('resize', resize);

            var elapsed=0, last=0;
            var APPROACH=2.6, CAM_START=3.6, ASM_DUR=1.15, PEAK=2.05+0.85+1.15;
            var REVEAL_AT=PEAK+1.7;
            var STIFF=30, DAMP=9, R=120, FORCE=2600;
            var interactive=false, flash=0, ringOn=false, ringT=0, burst=false, revealedOnce=false;
            function camDist(t){ var p=Math.min(t/APPROACH,1); var b=1+(CAM_START-1)*(1-easeOut(p)); if(t>APPROACH) b+=Math.sin(t*0.6)*0.012; return b; }
            function proj(nd,cam,pX,pY){ var fwx=(nd.fnx+0.05*Math.sin(elapsed*nd.ds+nd.da))*minDim*SPREAD;
                var fwy=(nd.fny+0.05*Math.cos(elapsed*nd.ds*0.9+nd.da))*minDim*SPREAD; var dep=nd.fz*cam, inv=1/dep;
                return { x:cx+(fwx+pX)*inv, y:cy+(fwy+pY)*inv, s:inv }; }
            function initPos(){ var cam=camDist(0); for(var i=0;i<nodes.length;i++){ var p=proj(nodes[i],cam,0,0); nodes[i].x=p.x; nodes[i].y=p.y; nodes[i].vx=0; nodes[i].vy=0; } }
            var grid={}, cell=80; function gk(a,b){ return a+'|'+b; }

            function frame(now){
                requestAnimationFrame(frame);
                var dt=(now-last)/1000; last=now; if(!(dt>0)) dt=0.016; if(dt>0.05) dt=0.05;
                elapsed+=dt; pSpeed*=Math.exp(-dt*3); flash*=Math.exp(-dt*2.4);
                if(!revealedOnce && elapsed>=REVEAL_AT){ revealedOnce=true; clearTimeout(safety); revealBox(); }

                var cam=camDist(elapsed);
                var settle=clamp((elapsed-APPROACH)/1.2,0,1);
                var pX=Math.sin(elapsed*0.25)*16*settle, pY=Math.cos(elapsed*0.21)*11*settle;
                if(!interactive && elapsed>3.1) interactive=true;
                if(!burst && elapsed>=PEAK){ burst=true; flash=1; ringOn=true; ringT=0; for(var bs=0;bs<signals.length;bs++) if(Math.random()<0.7) relocate(signals[bs]); }

                var trail=0.14+0.42*settle;
                ctx.globalCompositeOperation='source-over'; ctx.globalAlpha=1;
                ctx.fillStyle='rgba(12,15,12,'+trail+')'; ctx.fillRect(0,0,W,H);
                ctx.globalCompositeOperation='lighter';

                var inten=1+Math.min(pSpeed,2.4), useMouse=interactive&&hovering, damp=Math.exp(-DAMP*dt);

                animalTimer-=dt;
                if(animalTimer<=0 && bgAnimals.length<MAX_ANIMALS && elapsed>PEAK-1){ spawnAnimal(); animalTimer=rnd(3,5); }
                for(var ax=bgAnimals.length-1; ax>=0; ax--){
                    var an=bgAnimals[ax]; an.life+=dt; an.px+=an.vx*dt; an.py+=an.vy*dt;
                    if(an.life>=an.maxLife){ bgAnimals.splice(ax,1); continue; }
                    var lifeA=Math.min(an.life/2,1)*clamp((an.maxLife-an.life)/2,0,1), dA=Math.exp(-7*dt);
                    for(var pi=0;pi<an.parts.length;pi++){ var pp=an.parts[pi];
                        var tgx=an.px+pp.nx*an.scale, tgy=an.py+pp.ny*an.scale, axx=(tgx-pp.x)*22, ayy=(tgy-pp.y)*22;
                        if(useMouse){ var qx=pp.x-mx, qy=pp.y-my, di=Math.sqrt(qx*qx+qy*qy)+0.001; if(di<R){ var f=(1-di/R); f=f*f*1700*inten; axx+=qx/di*f; ayy+=qy/di*f; } }
                        pp.vx+=axx*dt; pp.vy+=ayy*dt; pp.vx*=dA; pp.vy*=dA; pp.x+=pp.vx*dt; pp.y+=pp.vy*dt;
                        var tk=0.85+0.15*Math.sin(elapsed*pp.twS+pp.tw), spr=pp.accent?gAccent:(an.col==='g'?gNet:gSignal);
                        ctx.globalAlpha=lifeA*0.6*tk; var rr=2.7*tk; ctx.drawImage(spr,pp.x-rr,pp.y-rr,rr*2,rr*2); }
                }

                cell=Math.max(50,0.075*minDim); grid={};
                for(var i=0;i<nodes.length;i++){
                    var nd=nodes[i]; var fp=proj(nd,cam,pX,pY); var tgX=fp.x, tgY=fp.y, sc=fp.s;
                    if(nd.recruited){ var b=easeIO(clamp((elapsed-nd.t0)/ASM_DUR,0,1));
                        var nameX=cx+nd.tx*scaleLayout+pX, nameY=cy+nd.ty*scaleLayout+pY-H*0.13;
                        tgX=fp.x+(nameX-fp.x)*b; tgY=fp.y+(nameY-fp.y)*b; sc=fp.s+((1/cam)-fp.s)*b; nd.assembled=b; }
                    var ax2=(tgX-nd.x)*STIFF, ay2=(tgY-nd.y)*STIFF;
                    if(useMouse){ var ddx=nd.x-mx, ddy=nd.y-my, dist=Math.sqrt(ddx*ddx+ddy*ddy)+0.001;
                        if(dist<R){ var fr=(1-dist/R); fr=fr*fr*FORCE*inten; var ux=ddx/dist, uy=ddy/dist; ax2+=ux*fr; ay2+=uy*fr; ax2+=-uy*fr*0.5; ay2+=ux*fr*0.5; } }
                    nd.vx+=ax2*dt; nd.vy+=ay2*dt; nd.vx*=damp; nd.vy*=damp; nd.x+=nd.vx*dt; nd.y+=nd.vy*dt;
                    nd.scale=sc; nd.alpha=Math.min(sc*1.15,1);
                    var gx=(nd.x/cell)|0, gy=(nd.y/cell)|0, key=gk(gx,gy); (grid[key]||(grid[key]=[])).push(i);
                    nd._gx=gx; nd._gy=gy; nd._links=0; nd._nc=0;
                }

                var linkD=cell, linkD2=linkD*linkD, CAPL=4; ctx.lineWidth=1;
                for(var n=0;n<nodes.length;n++){ var a=nodes[n]; if(a._links>=CAPL||a.alpha<=0.02) continue;
                    for(var ox=-1;ox<=1;ox++)for(var oy=-1;oy<=1;oy++){ var bucket=grid[gk(a._gx+ox,a._gy+oy)]; if(!bucket) continue;
                        for(var q=0;q<bucket.length;q++){ var bi=bucket[q]; if(bi<=n) continue; var b2=nodes[bi]; if(b2.alpha<=0.02) continue;
                            var dx=a.x-b2.x, dy=a.y-b2.y, dd=dx*dx+dy*dy;
                            if(dd<linkD2){ var d3=Math.sqrt(dd); if(a._nc<6)a._nbr[a._nc++]=bi; if(b2._nc<6)b2._nbr[b2._nc++]=n;
                                if(b2._links<CAPL){ var nm=((a.recruited&&a.assembled>0.5)||(b2.recruited&&b2.assembled>0.5));
                                    ctx.strokeStyle=nm?'rgb(225,158,72)':'rgb(86,128,72)';
                                    ctx.globalAlpha=(1-d3/linkD)*(nm?0.30:0.16)*Math.min(a.alpha,b2.alpha);
                                    ctx.beginPath(); ctx.moveTo(a.x,a.y); ctx.lineTo(b2.x,b2.y); ctx.stroke(); a._links++; b2._links++; }
                                if(a._links>=CAPL){ ox=2; oy=2; break; } } } } }

                for(var m2=0;m2<nodes.length;m2++){ var p=nodes[m2]; if(p.alpha<=0.02) continue;
                    var nm2=p.recruited&&p.assembled>0.45, spr2=p.accent?gAccent:(nm2?gName:gNet), tk2=0.9+0.1*Math.sin(elapsed*p.twS+p.tw);
                    var al=Math.min(1,p.alpha*(nm2?1:0.8)*tk2*(1+flash*0.4)); ctx.globalAlpha=al;
                    var rr=(p.size+(nm2?0.9:0.7))*p.scale*2.5*(1+flash*0.6); if(rr<0.6) rr=0.6;
                    ctx.drawImage(spr2,p.x-rr,p.y-rr,rr*2,rr*2); }

                if(useMouse){ for(var cf=0;cf<3;cf++){ var cand=(Math.random()*nodes.length)|0, cn=nodes[cand];
                    if(Math.hypot(cn.x-mx,cn.y-my)<R*1.3){ var sg=signals[(Math.random()*signals.length)|0]; sg.ai=cand; sg.bi=-1; sg.t=0; } } }

                var sigGlow=0.25+0.6*Math.min(settle+(burst?0.3:0),1);
                for(var si=0;si<signals.length;si++){ var sig=signals[si];
                    if(sig.t>=1){ if(sig.bi>=0) sig.ai=sig.bi; sig.bi=-1; sig.t=0; }
                    if(sig.bi<0){ var na0=nodes[sig.ai]; if(na0._nc>0) sig.bi=na0._nbr[(Math.random()*na0._nc)|0];
                        else { relocate(sig); na0=nodes[sig.ai]; if(na0._nc>0) sig.bi=na0._nbr[(Math.random()*na0._nc)|0]; } }
                    if(sig.bi<0) continue; sig.t+=sig.speed*dt; var na=nodes[sig.ai], nb=nodes[sig.bi], tt=Math.min(sig.t,1);
                    var sx=na.x+(nb.x-na.x)*tt, sy=na.y+(nb.y-na.y)*tt, bt=Math.max(tt-0.18,0), bx=na.x+(nb.x-na.x)*bt, by=na.y+(nb.y-na.y)*bt;
                    ctx.strokeStyle=sig.accent?'rgb(245,190,110)':'rgb(150,200,120)'; ctx.globalAlpha=0.42*sigGlow; ctx.lineWidth=1.4;
                    ctx.beginPath(); ctx.moveTo(bx,by); ctx.lineTo(sx,sy); ctx.stroke();
                    ctx.globalAlpha=0.9*sigGlow; ctx.drawImage(gSignal,sx-3,sy-3,6,6); }

                if(ringOn){ ringT+=dt; var rp=ringT/0.95; if(rp>=1) ringOn=false; var diag=Math.hypot(W,H);
                    var rad=easeOut(Math.min(rp,1))*diag*0.6, al2=(1-Math.min(rp,1))*0.4;
                    ctx.strokeStyle='rgb(230,150,70)'; ctx.globalAlpha=al2; ctx.lineWidth=2+6*(1-rp);
                    ctx.beginPath(); ctx.arc(cx,cy,rad,0,6.2832); ctx.stroke(); }
                if(flash>0.01){ var rg=ctx.createRadialGradient(cx,cy,0,cx,cy,minDim*0.95);
                    rg.addColorStop(0,'rgba(255,210,150,'+(flash*0.16)+')'); rg.addColorStop(1,'rgba(255,210,150,0)');
                    ctx.globalAlpha=1; ctx.fillStyle=rg; ctx.fillRect(0,0,W,H); }
                ctx.globalAlpha=1; ctx.globalCompositeOperation='source-over';
            }

            build(); initSignals(); resize(); initPos();
            last=performance.now(); requestAnimationFrame(frame);
        }
    })();
    </script>
</body>
</html>
