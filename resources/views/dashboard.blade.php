@extends('layouts.app')

@section('title', 'Guia de Fornecedores — ' . config('app.name'))

@section('styles')
<style>
    /* ── Header ─────────────────────────────── */
    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -.02em; margin-bottom: 0.5rem; }
    .page-header p  { font-size: 0.875rem; color: #999; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #ebebeb; }

    /* ── Toolbar ─────────────────────────────── */
    .toolbar {
        display: flex;
        gap: 0.625rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .search-wrap {
        position: relative;
        flex: 1;
        min-width: 220px;
        max-width: 360px;
    }

    .search-wrap svg {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #bbb;
        pointer-events: none;
    }

    .search-wrap input {
        width: 100%;
        padding: 0.6rem 0.75rem 0.6rem 2.25rem;
        border: 1.5px solid #e8e8e8;
        border-radius: 10px;
        font-size: 0.875rem;
        outline: none;
        background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .search-wrap input:focus {
        border-color: #1a1a1a;
        box-shadow: 0 0 0 3px rgba(0,0,0,.06);
    }

    .toolbar select {
        padding: 0.6rem 0.875rem;
        border: 1.5px solid #e8e8e8;
        border-radius: 10px;
        font-size: 0.875rem;
        outline: none;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s;
        color: #444;
    }

    .toolbar select:focus { border-color: #1a1a1a; }

    /* ── Count ─────────────────────────────── */
    .count-bar {
        font-size: 0.8rem;
        color: #aaa;
        margin-bottom: 1.25rem;
    }

    /* ── Grid de cards ───────────────────────── */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .card {
        background: #fff;
        border: 1.5px solid #ebebeb;
        border-radius: 12px;
        padding: 1.25rem 1.375rem;
        display: flex;
        flex-direction: column;
        gap: 0.875rem;
        transition: box-shadow 0.15s, border-color 0.15s;
    }

    .card:hover {
        border-color: #d5d5d5;
        box-shadow: 0 4px 16px rgba(0,0,0,.07);
    }

    .card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .supplier-name {
        font-size: 0.975rem;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.3;
    }

    .nota {
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #1a1a1a;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .nota .star { color: #f5a623; font-size: 0.9rem; }

    .card-tags { display: flex; flex-wrap: wrap; gap: 0.3rem; }

    .tag {
        display: inline-block;
        padding: 0.22em 0.65em;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .tag-default { background: #f0f0ee; color: #555; }
    .tag-gray    { background: #e8e8e5; color: #555; }
    .tag-brown   { background: #f0e4dc; color: #7b5343; }
    .tag-orange  { background: #fde8d0; color: #b35f1e; }
    .tag-yellow  { background: #fef0cc; color: #9b6e0c; }
    .tag-green   { background: #d8edda; color: #2e6b3e; }
    .tag-blue    { background: #d0e8f5; color: #1a618d; }
    .tag-purple  { background: #e8ddf5; color: #6b3fa0; }
    .tag-pink    { background: #f5dced; color: #a8355e; }
    .tag-red     { background: #ffdedd; color: #b9342c; }

    .card-meta {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .meta-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.825rem;
        color: #666;
    }

    .meta-row svg { flex-shrink: 0; color: #bbb; }

    .whatsapp-link {
        color: #1a1a1a;
        text-decoration: none;
        font-size: 0.825rem;
        transition: color 0.1s;
    }

    .whatsapp-link:hover { color: #25D366; }

    .empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 0;
        color: #bbb;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
    @php
        $rows = collect($suppliers)->map(function ($item) {
            $p = $item['properties'];
            return [
                'nome'      => $p['Nome do fornecedor']['title'][0]['plain_text'] ?? '',
                'categoria' => $p['Categoria']['select'] ?? null,
                'cidade'    => $p['Cidade / Região (padrão)']['select']['name'] ?? ($p['Cidade / Região de atuação']['rich_text'][0]['plain_text'] ?? ''),
                'whatsapp'  => $p['Qual WhatsApp você usa pra falar com esse fornecedor?']['phone_number'] ?? '',
                'destaques' => collect($p['Destaques (comunidade)']['rollup']['array'] ?? [])->pluck('multi_select')->flatten(1)->all(),
                'nota'      => $p['Nota média (comunidade)']['rollup']['number'] ?? null,
            ];
        })->filter(fn($r) => $r['nome'] !== '')->values();
    @endphp

    <div class="page-header">
        <h1>Guia de Fornecedores</h1>
        <p>{{ $rows->count() }} fornecedores cadastrados pela comunidade</p>
    </div>

    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="search" placeholder="Buscar por nome...">
        </div>

        <select id="filter-cat">
            <option value="">Todas as categorias</option>
            @foreach($rows->pluck('categoria')->filter()->unique('name')->sortBy('name') as $cat)
                <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
            @endforeach
        </select>

        <select id="filter-cidade">
            <option value="">Todas as cidades</option>
            @foreach($rows->pluck('cidade')->filter()->unique()->sort() as $cidade)
                <option value="{{ $cidade }}">{{ $cidade }}</option>
            @endforeach
        </select>
    </div>

    <p class="count-bar" id="count">Mostrando {{ $rows->count() }} fornecedores</p>

    <div class="grid" id="grid">
        @forelse($rows as $row)
            <div class="card"
                data-nome="{{ strtolower($row['nome']) }}"
                data-cat="{{ $row['categoria']['name'] ?? '' }}"
                data-cidade="{{ $row['cidade'] }}"
            >
                <div class="card-top">
                    <span class="supplier-name">{{ $row['nome'] }}</span>
                    @if($row['nota'] !== null)
                        <span class="nota"><span class="star">★</span> {{ number_format($row['nota'], 1) }}</span>
                    @endif
                </div>

                <div class="card-tags">
                    @if($row['categoria'])
                        <span class="tag tag-{{ $row['categoria']['color'] ?? 'default' }}">
                            {{ $row['categoria']['name'] }}
                        </span>
                    @endif
                    @foreach($row['destaques'] as $d)
                        <span class="tag tag-{{ $d['color'] ?? 'default' }}">{{ $d['name'] }}</span>
                    @endforeach
                </div>

                <div class="card-meta">
                    @if($row['cidade'])
                        <div class="meta-row">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            {{ $row['cidade'] }}
                        </div>
                    @endif
                    @if($row['whatsapp'])
                        <div class="meta-row">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16.27a2 2 0 0 1 .27.65Z"/></svg>
                            <a class="whatsapp-link"
                               href="https://wa.me/{{ preg_replace('/\D/', '', $row['whatsapp']) }}"
                               target="_blank" rel="noopener">
                                {{ $row['whatsapp'] }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">Nenhum fornecedor encontrado.</div>
        @endforelse
    </div>

    <script>
        const search    = document.getElementById('search');
        const filterCat = document.getElementById('filter-cat');
        const filterCid = document.getElementById('filter-cidade');
        const cards     = document.querySelectorAll('#grid .card');
        const count     = document.getElementById('count');

        function applyFilters() {
            const q   = search.value.toLowerCase();
            const cat = filterCat.value;
            const cid = filterCid.value;
            let visible = 0;

            cards.forEach(card => {
                const show = card.dataset.nome.includes(q)
                    && (!cat || card.dataset.cat === cat)
                    && (!cid || card.dataset.cidade === cid);
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            count.textContent = 'Mostrando ' + visible + ' fornecedor' + (visible !== 1 ? 'es' : '');
        }

        search.addEventListener('input', applyFilters);
        filterCat.addEventListener('change', applyFilters);
        filterCid.addEventListener('change', applyFilters);
    </script>
@endsection
