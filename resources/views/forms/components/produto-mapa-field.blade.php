@php
    /*use Illuminate\Support\Facades\Http;

    // $etapas = $this->getRecord()->etapas->toArray() ?? [];
    $etapas_mapped = array_map(function($etapa) {
        return [$etapa["departamento_id_origem_nome"], $etapa["departamento_id_destino_nome"], $etapa["tempo_producao"]];
    }, $etapas);

    $digraph = "digraph Producao {  graph [splines=polyline, nodesep=0.75]; node [shape=box, fixedsize=true, width=4, height=0.5]; ";
    foreach ($etapas_mapped as $etapa) {
        $digraph .= "\"{$etapa[0]}\" -> \"{$etapa[1]}\"";
        $label = empty($etapa[2]) ? "-" : ($etapa[2] . "s");
        $digraph .= "[label=\" $label \"]";
        $digraph .= ";";
    }
    $digraph .= "}";

    $response = Http::post('https://quickchart.io/graphviz', [
        'graph' => $digraph,
        'layout' => 'dot',
        'format' => 'png',
    ]);

    $pngEncoded = "data:image/png;base64," . base64_encode($response->body());
    // dd($digraph);*/
@endphp

<div>
    <!-- Interact with the `state` property in Alpine.js -->
    <x-filament::section compact class="p-0">
        <div class="flex justify-center">
            <div class="max-w-fit">
                <!--<img src="$pngEncoded" alt="Mapa de Produção" />-->
                asd
            </div>
        </div>
    </x-filament::section>
</div>

