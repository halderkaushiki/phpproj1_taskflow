(()=>{
const C=window.APP||{},$=(s,r=document)=>r.querySelector(s),K='taskflow';
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>'&#'+c.charCodeAt(0)+';');
const fd=f=>Object.fromEntries(new FormData(f));
const S=t=>{try{return JSON.parse(t.subs)||[]}catch{return[]}};
const pay=(f,old=[])=>{const d=fd(f),l=(d.subtext||'').split('\n').map(x=>x.trim()).filter(Boolean);d.subs=JSON.stringify(l.map(t=>({t,d:(old.find(x=>x.t===t)||{}).d||0})));delete d.subtext;return d};
/* ---- API: PHP backend, or localStorage when C.demo ---- */
const remote=async(a,b)=>{const r=await fetch('api.php?a='+a,{method:b?'POST':'GET',headers:{'Content-Type':'application/json','X-CSRF':C.csrf},body:b?JSON.stringify(b):undefined});if(r.status===401)location='login.php';if(!r.ok)throw 0;return r.json()};
let mem=[];try{mem=JSON.parse(localStorage.getItem(K))||[]}catch{}
if(C.demo&&!mem.length){const d=n=>new Date(Date.now()+n*864e5).toLocaleDateString('en-CA');
mem=[{id:3,title:'Finish project report',notes:'Add ER diagram and screenshots',priority:'high',category:'college',due:d(1),done:0},{id:2,title:'Buy groceries',notes:'',priority:'low',category:'home',due:d(0),done:0},{id:1,title:'Read one chapter',notes:'',priority:'medium',category:'study',due:'',done:1}]}
const local=async(a,b={})=>{
if(a==='add')mem.unshift({id:Date.now(),done:0,...b});
const x=mem.find(t=>t.id==b.id);
if(a==='update'&&x)Object.assign(x,b,{id:x.id});
if(a==='toggle'&&x)x.done=+x.done?0:1;
if(a==='pin'&&x)x.pinned=+x.pinned?0:1;if(a==='subs'&&x)x.subs=b.subs;
if(a==='delete')mem=mem.filter(t=>t.id!=b.id);
if(a==='clear')mem=mem.filter(t=>!+t.done);
try{localStorage.setItem(K,JSON.stringify(mem))}catch{}
return mem};
const api=C.demo?local:remote;
/* ---- UI ---- */
let T=[],q='',f='all',s='new',cat='';
const today=new Date().toLocaleDateString('en-CA');
const FLD=`<textarea name="notes" rows="2" placeholder="Notes (optional)"></textarea><textarea name="subtext" rows="2" placeholder="Checklist, one item per line (optional)"></textarea><div class="row"><select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select><input name="category" placeholder="Category" list="dl" maxlength="30"><input type="date" name="due"></div>`;
$('#app').innerHTML=`<header><div><h1>Hi, ${esc(C.name)} 👋</h1><p>${new Date().toLocaleDateString(undefined,{weekday:'long',day:'numeric',month:'long'})}</p></div><div><button class="ic" id="th" title="Theme">◐</button>${C.demo?'<button class="lo" id="lo">Log out</button>':'<a class="lo" href="login.php?logout">Log out</a>'}</div></header>
<section class="card" id="st"></section>
<section class="card" id="fc"><div><small>Focus timer</small><strong id="ft">25:00</strong></div><div><button class="btn" id="fs">Start focus</button><button class="lnk" id="fr">Reset</button></div></section>
<form class="card" id="add"><div class="r1"><input name="title" placeholder="What needs doing?  (press N)" maxlength="200" required autocomplete="off"><button class="btn">Add</button></div><button type="button" class="lnk" id="more">+ details</button><div class="det">${FLD}</div></form>
<div class="bar"><input id="q" type="search" placeholder="Search  ( / )"><select id="cs"></select><select id="so"><option value="new">Newest</option><option value="due">Due date</option><option value="pri">Priority</option></select></div>
<nav class="chips"><button data-f="all" class="on">All</button><button data-f="active">Active</button><button data-f="today">Today</button><button data-f="late">Overdue</button><button data-f="done">Done</button></nav>
<ul id="ls"></ul>
<div class="foot"><button class="lnk" id="clr">Clear completed</button>${C.demo?'':'<button class="lnk" id="exp">Export CSV</button>'}</div>
<footer class="ft">© ${new Date().getFullYear()} TaskFlow · Built by Kaushiki Halder</footer>
<dialog id="dlg"><form id="ed"><h2>Edit task</h2><input name="title" maxlength="200" required>${FLD}<div class="acts"><button type="button" class="lnk" id="cx">Cancel</button><button class="btn">Save</button></div></form></dialog>
<div id="tt"></div><datalist id="dl"></datalist>`;
const dueTxt=d=>{const n=Math.round((new Date(d)-new Date(today))/864e5);return n===0?'Today':n===1?'Tomorrow':n===-1?'Yesterday':new Date(d+'T00:00').toLocaleDateString(undefined,{day:'numeric',month:'short'})};
const li=t=>{const sl=S(t),late=!+t.done&&t.due&&t.due<today;return`<li class="${+t.done?'done ':''}p-${t.priority}" data-id="${t.id}"><button class="chk" aria-label="Toggle done">${+t.done?'✓':''}</button><div class="main"><b>${esc(t.title)}</b>${t.notes?`<p>${esc(t.notes)}</p>`:''}${sl.length?`<div class="sbs">${sl.map((x,i)=>`<label><input type="checkbox" data-s="${i}" ${+x.d?'checked':''}> ${esc(x.t)}</label>`).join('')}</div>`:''}<div class="meta">${t.category?`<span>#${esc(t.category)}</span>`:''}${t.due?`<span class="${late?'late':''}">${late?'⚠ ':''}${dueTxt(t.due)}</span>`:''}${sl.length?`<span>${sl.filter(x=>+x.d).length}/${sl.length}</span>`:''}<span>${t.priority}</span></div></div><button class="ic star ${+t.pinned?'on':''}" data-a="pin" title="Pin">${+t.pinned?'★':'☆'}</button><button class="ic" data-a="edit" title="Edit">✎</button><button class="ic" data-a="del" title="Delete">🗑</button></li>`};
const view=()=>{const w={high:0,medium:1,low:2};
return T.filter(t=>(f==='all'||(f==='done'?!!+t.done:!+t.done&&(f==='active'||(f==='today'?t.due===today:t.due&&t.due<today))))&&(!cat||t.category===cat)&&(t.title+t.notes+t.category).toLowerCase().includes(q))
.sort((a,b)=>(a.done-b.done)||((+b.pinned||0)-(+a.pinned||0))||(s==='due'?(a.due||'9').localeCompare(b.due||'9'):s==='pri'?w[a.priority]-w[b.priority]:b.id-a.id))};
const draw=()=>{
const dn=T.filter(t=>+t.done).length,tot=T.length,p=tot?Math.round(dn/tot*100):0,late=T.filter(t=>!+t.done&&t.due&&t.due<today).length;
$('#st').innerHTML=`<svg viewBox="0 0 64 64" class="ring"><circle cx="32" cy="32" r="26"/><circle cx="32" cy="32" r="26" class="fg" style="stroke-dasharray:${p*1.634} 163.4"/><text x="32" y="37">${p}%</text></svg><div class="nums"><div><b>${tot-dn}</b>to do</div><div><b>${dn}</b>done</div><div><b class="${late?'late':''}">${late}</b>overdue</div></div>`;
const cats=[...new Set(T.map(t=>t.category).filter(Boolean))];if(cat&&!cats.includes(cat))cat='';
$('#dl').innerHTML=cats.map(c=>`<option value="${esc(c)}">`).join('');
$('#cs').innerHTML='<option value="">All categories</option>'+cats.map(c=>`<option ${c===cat?'selected':''}>${esc(c)}</option>`).join('');
const v=view();
$('#ls').innerHTML=v.length?v.map(li).join(''):`<li class="empty">${tot?'Nothing here. All caught up? 🎉':'No tasks yet. Add your first one above ✨'}</li>`};
const toast=(m,u)=>{const e=$('#tt');e.innerHTML=esc(m)+(u?' <button id="ud">Undo</button>':'');e.className='on';if(u)$('#ud').onclick=()=>{u();e.className=''};clearTimeout(toast.t);toast.t=setTimeout(()=>e.className='',5000)};
const run=async(a,b)=>{try{T=await api(a,b);draw()}catch{toast('Something went wrong')}};
$('#add').onsubmit=async e=>{e.preventDefault();await run('add',pay(e.target));e.target.reset();$('#add input').focus()};
$('#more').onclick=()=>$('#add').classList.toggle('open');
$('#ls').onclick=e=>{const el=e.target.closest('li[data-id]');if(!el)return;const id=el.dataset.id,t=T.find(x=>x.id==id);
if(e.target.dataset.s!==undefined){const a=S(t),i=e.target.dataset.s;a[i].d=+a[i].d?0:1;run('subs',{id,subs:JSON.stringify(a)})}
else if(e.target.closest('.chk'))run('toggle',{id});
else if(e.target.closest('[data-a=pin]'))run('pin',{id});
else if(e.target.closest('[data-a=del]')){run('delete',{id});toast('Task deleted',()=>run('add',t))}
else if(e.target.closest('[data-a=edit]')){const F=$('#ed');for(const k of['title','notes','priority','category','due'])F.elements[k].value=t[k]||'';F.elements.subtext.value=S(t).map(x=>x.t).join('\n');F.dataset.id=id;$('#dlg').showModal()}};
$('#ed').onsubmit=e=>{e.preventDefault();run('update',{...pay(e.target,S(T.find(x=>x.id==e.target.dataset.id)||{})),id:e.target.dataset.id});$('#dlg').close()};
$('#cx').onclick=()=>$('#dlg').close();
$('.chips').onclick=e=>{const b=e.target.closest('button');if(!b)return;f=b.dataset.f;document.querySelectorAll('.chips button').forEach(x=>x.classList.toggle('on',x===b));draw()};
$('#q').oninput=e=>{q=e.target.value.toLowerCase();draw()};
$('#cs').onchange=e=>{cat=e.target.value;draw()};
$('#so').onchange=e=>{s=e.target.value;draw()};
$('#clr').onclick=()=>run('clear',{});
if($('#exp'))$('#exp').onclick=()=>{const r=[['Title','Notes','Priority','Category','Due','Done'],...T.map(t=>[t.title,t.notes,t.priority,t.category,t.due,+t.done?'yes':'no'])];const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([r.map(x=>x.map(v=>'"'+String(v??'').replace(/"/g,'""')+'"').join(',')).join('\n')],{type:'text/csv'}));a.download='tasks.csv';a.click()};
try{const t=localStorage.getItem(K+'-t');if(t)document.documentElement.dataset.theme=t}catch{}
$('#th').onclick=()=>{const r=document.documentElement,d=(r.dataset.theme||(matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'))==='dark';r.dataset.theme=d?'light':'dark';try{localStorage.setItem(K+'-t',r.dataset.theme)}catch{}};
addEventListener('keydown',e=>{if(/INPUT|TEXTAREA|SELECT/.test(e.target.tagName)||e.metaKey||e.ctrlKey)return;if(e.key==='n'){e.preventDefault();$('#add input').focus()}if(e.key==='/'){e.preventDefault();$('#q').focus()}});
let rem=1500,iv=null;
const tm=()=>{const m=String(Math.floor(rem/60)).padStart(2,'0'),c=String(rem%60).padStart(2,'0');$('#ft').textContent=m+':'+c;document.title=iv?m+':'+c+' · TaskFlow':'TaskFlow'};
$('#fs').onclick=()=>{if(iv){clearInterval(iv);iv=null;$('#fs').textContent='Resume'}else{$('#fs').textContent='Pause';iv=setInterval(()=>{if(--rem<=0){clearInterval(iv);iv=null;rem=1500;$('#fs').textContent='Start focus';toast('Focus session complete 🎉 Take a 5 minute break')}tm()},1000)}tm()};
$('#fr').onclick=()=>{clearInterval(iv);iv=null;rem=1500;$('#fs').textContent='Start focus';tm()};
run('list').then(()=>{const l=$('#ld');l&&l.classList.add('off')});
})();
