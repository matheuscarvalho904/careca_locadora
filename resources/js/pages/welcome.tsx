import { Head } from '@inertiajs/react';
import { ArrowRight, CalendarDays, CarFront, Fuel, Gauge, MapPin, Menu, Search, ShieldCheck, Users, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

type Branch = { id: string; name: string; city?: string | null; state?: string | null };
type Category = { id: string; name: string };
type Vehicle = {
    id: string; prefix: string; name: string; seats?: number | null;
    transmission?: string | null; fuel_type?: string | null; model_year?: number | null;
    category?: { id?: string | null; name?: string | null };
    branch?: { id?: string | null; name?: string | null; city?: string | null; state?: string | null };
    photos?: Array<{ path: string; featured: boolean }>;
};
type Quote = { total_value?: number; deposit_value?: number };

const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
const localDate = (date: Date) => {
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
};
const photoUrl = (vehicle: Vehicle) => {
    const photo = vehicle.photos?.find((item) => item.featured) ?? vehicle.photos?.[0];
    if (!photo?.path) return null;
    if (/^https?:\/\//.test(photo.path)) return photo.path;
    return `/storage/${photo.path.replace(/^public\//, '')}`;
};

export default function Welcome() {
    const defaults = useMemo(() => {
        const start = new Date(); start.setDate(start.getDate()+1); start.setHours(8,0,0,0);
        const end = new Date(start); end.setDate(end.getDate()+3); end.setHours(18,0,0,0);
        return { start: localDate(start), end: localDate(end) };
    }, []);

    const [branches, setBranches] = useState<Branch[]>([]);
    const [categories, setCategories] = useState<Category[]>([]);
    const [vehicles, setVehicles] = useState<Vehicle[]>([]);
    const [quotes, setQuotes] = useState<Record<string, Quote>>({});
    const [loading, setLoading] = useState(false);
    const [quoteLoading, setQuoteLoading] = useState<string | null>(null);
    const [searched, setSearched] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const [message, setMessage] = useState<string | null>(null);
    const [form, setForm] = useState({ branch_id:'', category_id:'', starts_at:defaults.start, ends_at:defaults.end, search:'' });

    useEffect(() => {
        Promise.all([
            fetch('/api/public/branches').then((r) => r.json()),
            fetch('/api/public/categories').then((r) => r.json()),
        ]).then(([b,c]) => { setBranches(b.data ?? []); setCategories(c.data ?? []); })
          .catch(() => setMessage('Não foi possível carregar lojas e categorias.'));
    }, []);

    const update = (field: keyof typeof form, value: string) => setForm((current) => ({ ...current, [field]: value }));

    const searchVehicles = async () => {
        setLoading(true); setSearched(true); setMessage(null); setQuotes({});
        try {
            const response = await fetch('/api/public/availability', {
                method:'POST', headers:{ Accept:'application/json','Content-Type':'application/json' },
                body:JSON.stringify({ ...form, branch_id:form.branch_id || null, category_id:form.category_id || null, search:form.search || null }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message ?? 'Falha ao consultar disponibilidade.');
            setVehicles(payload.data ?? []);
            if (!(payload.data ?? []).length) setMessage('Nenhum veículo disponível para o período informado.');
        } catch (error) {
            setVehicles([]); setMessage(error instanceof Error ? error.message : 'Falha ao consultar disponibilidade.');
        } finally { setLoading(false); }
    };

    const calculateQuote = async (vehicle: Vehicle) => {
        if (!vehicle.category?.id) return setMessage('Veículo sem categoria comercial configurada.');
        setQuoteLoading(vehicle.id); setMessage(null);
        try {
            const response = await fetch('/api/public/quote', {
                method:'POST', headers:{ Accept:'application/json','Content-Type':'application/json' },
                body:JSON.stringify({ branch_id:(vehicle.branch?.id ?? form.branch_id) || null, category_id:vehicle.category.id, starts_at:form.starts_at, ends_at:form.ends_at }),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message ?? 'Não foi possível calcular o valor.');
            setQuotes((current) => ({ ...current, [vehicle.id]:payload.data }));
        } catch (error) { setMessage(error instanceof Error ? error.message : 'Não foi possível calcular o valor.'); }
        finally { setQuoteLoading(null); }
    };

    return <>
        <Head title="Aluguel de veículos" />
        <div className="min-h-screen bg-[#f5f2eb] text-zinc-950">
            <header className="sticky top-0 z-50 bg-[#08090b]/95 text-white backdrop-blur">
                <div className="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
                    <a href="/" className="flex items-center gap-3"><span className="grid size-11 place-items-center rounded-2xl bg-red-600"><CarFront /></span><span><b className="block text-lg">Careca Locadora</b><small className="text-[10px] tracking-[.2em] text-zinc-400 uppercase">Veículos e soluções</small></span></a>
                    <nav className="hidden gap-8 text-sm font-semibold lg:flex"><a href="#reserva">Reservar</a><a href="#frota">Frota</a><a href="#vantagens">Vantagens</a><a href="#lojas">Lojas</a></nav>
                    <div className="hidden gap-3 lg:flex"><a href="/login" className="rounded-full border border-white/15 px-5 py-2.5 text-sm font-bold">Área do cliente</a><a href="/app" className="rounded-full bg-red-600 px-5 py-2.5 text-sm font-bold">Painel administrativo</a></div>
                    <button type="button" onClick={() => setMenuOpen(!menuOpen)} className="rounded-xl border border-white/15 p-2 lg:hidden">{menuOpen ? <X/> : <Menu/>}</button>
                </div>
                {menuOpen && <div className="grid gap-4 border-t border-white/10 px-5 py-5 text-sm font-semibold lg:hidden"><a href="#reserva">Reservar</a><a href="#frota">Frota</a><a href="#vantagens">Vantagens</a><a href="/login">Área do cliente</a><a href="/app" className="text-red-400">Painel administrativo</a></div>}
            </header>

            <main>
                <section className="relative overflow-hidden bg-[#08090b] text-white">
                    <div className="absolute inset-0 opacity-60 [background-image:radial-gradient(circle_at_75%_30%,rgba(220,38,38,.45),transparent_30%),radial-gradient(circle_at_15%_90%,rgba(255,255,255,.1),transparent_28%)]" />
                    <div className="relative mx-auto grid min-h-[650px] max-w-7xl items-center gap-12 px-5 py-20 lg:grid-cols-2 lg:px-8">
                        <div><span className="rounded-full border border-red-400/30 bg-red-500/10 px-4 py-2 text-xs font-black tracking-[.16em] text-red-300 uppercase">Mobilidade sem complicação</span><h1 className="mt-7 text-5xl leading-[.95] font-black tracking-[-.055em] sm:text-6xl lg:text-7xl">Seu próximo caminho começa <span className="text-red-500">aqui.</span></h1><p className="mt-7 max-w-xl text-lg leading-8 text-zinc-300">Consulte disponibilidade real e reserve o veículo ideal com segurança.</p></div>
                        <div id="reserva" className="rounded-[2rem] bg-white p-6 text-zinc-950 shadow-2xl sm:p-8">
                            <p className="text-xs font-black tracking-[.18em] text-red-600 uppercase">Reserva online</p><h2 className="mt-2 text-2xl font-black">Onde você quer chegar?</h2>
                            <div className="mt-6 grid gap-4">
                                <label className="grid gap-2 text-xs font-bold">Loja de retirada<select value={form.branch_id} onChange={(e)=>update('branch_id',e.target.value)} className="h-14 rounded-2xl border border-zinc-200 bg-white px-4 text-sm"><option value="">Todas as lojas</option>{branches.map((b)=><option key={b.id} value={b.id}>{b.name}{b.city ? ` — ${b.city}/${b.state ?? ''}` : ''}</option>)}</select></label>
                                <div className="grid gap-4 sm:grid-cols-2"><label className="grid gap-2 text-xs font-bold">Retirada<input type="datetime-local" value={form.starts_at} onChange={(e)=>update('starts_at',e.target.value)} className="h-14 rounded-2xl border border-zinc-200 px-4 text-sm" /></label><label className="grid gap-2 text-xs font-bold">Devolução<input type="datetime-local" value={form.ends_at} onChange={(e)=>update('ends_at',e.target.value)} className="h-14 rounded-2xl border border-zinc-200 px-4 text-sm" /></label></div>
                                <label className="grid gap-2 text-xs font-bold">Categoria<select value={form.category_id} onChange={(e)=>update('category_id',e.target.value)} className="h-14 rounded-2xl border border-zinc-200 bg-white px-4 text-sm"><option value="">Todas as categorias</option>{categories.map((c)=><option key={c.id} value={c.id}>{c.name}</option>)}</select></label>
                                <button type="button" onClick={searchVehicles} disabled={loading} className="flex h-14 items-center justify-center gap-2 rounded-2xl bg-red-600 text-sm font-black text-white hover:bg-red-700 disabled:opacity-60">{loading ? 'Consultando...' : <>Pesquisar veículos <ArrowRight className="size-5"/></>}</button>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="vantagens" className="bg-white"><div className="mx-auto grid max-w-7xl divide-y divide-zinc-200 px-5 md:grid-cols-3 md:divide-x md:divide-y-0 lg:px-8">{[[ShieldCheck,'Segurança','Processo digital integrado.'],[Gauge,'Agilidade','Disponibilidade em tempo real.'],[MapPin,'Proximidade','Lojas e atendimento regional.']].map(([Icon,title,text]) => { const Component = Icon as typeof ShieldCheck; return <div key={String(title)} className="flex gap-4 px-3 py-8 md:px-8"><span className="grid size-12 place-items-center rounded-2xl bg-red-50 text-red-600"><Component/></span><div><b>{String(title)}</b><p className="mt-1 text-sm text-zinc-500">{String(text)}</p></div></div>; })}</div></section>

                <section id="frota" className="mx-auto max-w-7xl px-5 py-20 lg:px-8">
                    <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"><div><p className="text-xs font-black tracking-[.2em] text-red-600 uppercase">Catálogo integrado</p><h2 className="mt-3 text-4xl font-black tracking-[-.04em] sm:text-5xl">Uma frota para cada plano.</h2></div><div className="relative w-full lg:max-w-sm"><Search className="absolute top-1/2 left-4 size-5 -translate-y-1/2 text-zinc-400"/><input value={form.search} onChange={(e)=>update('search',e.target.value)} placeholder="Buscar modelo ou prefixo" className="h-14 w-full rounded-2xl border border-zinc-200 bg-white pl-12 pr-4 text-sm"/></div></div>
                    {message && <div className="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-900">{message}</div>}
                    {!searched && <div className="mt-10 grid min-h-72 place-items-center rounded-[2rem] border border-dashed border-zinc-300 bg-white/60 text-center"><div><CarFront className="mx-auto size-16 text-zinc-400"/><h3 className="mt-4 text-xl font-black">Faça sua primeira pesquisa</h3><p className="mt-2 text-sm text-zinc-500">Os veículos disponíveis aparecerão aqui.</p></div></div>}
                    {searched && vehicles.length > 0 && <div className="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">{vehicles.map((vehicle) => { const image=photoUrl(vehicle); const quote=quotes[vehicle.id]; return <article key={vehicle.id} className="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl"><div className="aspect-[16/10] bg-zinc-100">{image ? <img src={image} alt={vehicle.name} className="h-full w-full object-cover"/> : <div className="grid h-full place-items-center"><CarFront className="size-20 text-zinc-300"/></div>}</div><div className="p-6"><p className="text-xs font-black text-red-600">{vehicle.prefix}</p><h3 className="mt-1 text-2xl font-black">{vehicle.name}</h3><p className="mt-1 text-sm text-zinc-500">{vehicle.category?.name} · {vehicle.branch?.name}</p><div className="mt-5 grid grid-cols-3 gap-2 text-xs font-bold"><span className="rounded-xl bg-zinc-50 p-3"><Users className="mb-2 size-4 text-red-600"/>{vehicle.seats ?? '—'} lugares</span><span className="rounded-xl bg-zinc-50 p-3"><Gauge className="mb-2 size-4 text-red-600"/>{vehicle.transmission ?? 'Câmbio'}</span><span className="rounded-xl bg-zinc-50 p-3"><Fuel className="mb-2 size-4 text-red-600"/>{vehicle.fuel_type ?? 'Combustível'}</span></div><div className="mt-6 border-t border-zinc-100 pt-5">{quote ? <div className="flex items-end justify-between"><div><small className="text-zinc-500">Total estimado</small><div className="text-2xl font-black">{money.format(quote.total_value ?? 0)}</div><small className="text-zinc-500">Caução: {money.format(quote.deposit_value ?? 0)}</small></div><button className="rounded-xl bg-red-600 px-4 py-3 text-xs font-black text-white">Reservar</button></div> : <button type="button" onClick={()=>calculateQuote(vehicle)} disabled={quoteLoading===vehicle.id} className="flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-3.5 text-sm font-black text-white disabled:opacity-60">{quoteLoading===vehicle.id ? 'Calculando...' : 'Calcular valor'} <ArrowRight className="size-4"/></button>}</div></div></article>; })}</div>}
                </section>

                <section id="lojas" className="bg-[#111214] text-white"><div className="mx-auto grid max-w-7xl gap-10 px-5 py-16 lg:grid-cols-2 lg:px-8"><div><p className="text-xs font-black tracking-[.2em] text-red-400 uppercase">Presença regional</p><h2 className="mt-4 text-4xl font-black">Perto de você.</h2></div><div className="grid gap-3">{branches.slice(0,4).map((b)=><div key={b.id} className="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-5"><MapPin className="text-red-500"/><div><b>{b.name}</b><p className="text-sm text-zinc-400">{b.city}{b.state ? `/${b.state}` : ''}</p></div></div>)}</div></div></section>
            </main>
            <footer className="bg-[#08090b] text-zinc-400"><div className="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-8 text-sm md:flex-row md:justify-between lg:px-8"><span>© {new Date().getFullYear()} Careca Locadora de Veículos</span><span>Reserva online integrada ao ERP</span></div></footer>
        </div>
    </>;
}
