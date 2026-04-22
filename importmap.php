<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    'reveal.js' => [
        'version' => '6.0.1',
    ],
    'reveal.js/dist/reveal.css' => [
        'version' => '6.0.1',
        'type' => 'css',
    ],
    'reveal.js/dist/plugin/highlight.mjs' => [
        'version' => '6.0.1',
    ],
    'reveal.js/dist/plugin/highlight/monokai.css' => [
        'version' => '6.0.1',
        'type' => 'css',
    ],
    'mermaid' => [
        'version' => '11.14.0',
    ],
    'dayjs' => [
        'version' => '1.11.20',
    ],
    'khroma' => [
        'version' => '2.1.0',
    ],
    'dompurify' => [
        'version' => '3.3.3',
    ],
    'd3' => [
        'version' => '7.9.0',
    ],
    '@braintree/sanitize-url' => [
        'version' => '7.1.2',
    ],
    'lodash-es/memoize.js' => [
        'version' => '4.17.23',
    ],
    'lodash-es/merge.js' => [
        'version' => '4.17.23',
    ],
    '@iconify/utils' => [
        'version' => '3.1.0',
    ],
    'marked' => [
        'version' => '16.4.2',
    ],
    'ts-dedent' => [
        'version' => '2.2.0',
    ],
    'roughjs' => [
        'version' => '4.6.6',
    ],
    'stylis' => [
        'version' => '4.3.6',
    ],
    'lodash-es/isEmpty.js' => [
        'version' => '4.17.23',
    ],
    'katex' => [
        'version' => '0.16.44',
    ],
    'mermaid/dist/chunks/mermaid.core/dagre-KV5264BT.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/cose-bilkent-S5V4N54A.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/c4Diagram-AHTNJAMY.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/flowDiagram-DWJPFMVM.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/erDiagram-SMLLAGMA.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/gitGraphDiagram-UUTBAWPF.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/ganttDiagram-T4ZO3ILL.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/infoDiagram-42DDH7IO.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/pieDiagram-DEJITSTG.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/quadrantDiagram-34T5L4WZ.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/xychartDiagram-5P7HB3ND.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/requirementDiagram-MS252O5E.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/sequenceDiagram-FGHM5R23.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/classDiagram-6PBFFD2Q.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/classDiagram-v2-HSJHXN6E.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/stateDiagram-FHFEXIEX.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/stateDiagram-v2-QKLJ7IA2.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/journeyDiagram-VCZTEJTY.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/timeline-definition-GMOUNBTQ.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/mindmap-definition-QFDTVHPH.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/kanban-definition-6JOO6SKY.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/sankeyDiagram-XADWPNL6.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-TYMM5635.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-MMDJMWI5.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/blockDiagram-DXYQGD6D.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-5BDNPKRD.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/architectureDiagram-Q4EWVU46.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/ishikawaDiagram-UXIWVN3A.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/vennDiagram-DHZGUBPP.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/diagram-G4DWMVQ6.mjs' => [
        'version' => '11.14.0',
    ],
    'mermaid/dist/chunks/mermaid.core/wardleyDiagram-NUSXRM2D.mjs' => [
        'version' => '11.14.0',
    ],
    'd3-array' => [
        'version' => '3.2.4',
    ],
    'd3-axis' => [
        'version' => '3.0.0',
    ],
    'd3-brush' => [
        'version' => '3.0.0',
    ],
    'd3-chord' => [
        'version' => '3.0.1',
    ],
    'd3-color' => [
        'version' => '3.1.0',
    ],
    'd3-contour' => [
        'version' => '4.0.2',
    ],
    'd3-delaunay' => [
        'version' => '6.0.4',
    ],
    'd3-dispatch' => [
        'version' => '3.0.1',
    ],
    'd3-drag' => [
        'version' => '3.0.0',
    ],
    'd3-dsv' => [
        'version' => '3.0.1',
    ],
    'd3-ease' => [
        'version' => '3.0.1',
    ],
    'd3-fetch' => [
        'version' => '3.0.1',
    ],
    'd3-force' => [
        'version' => '3.0.0',
    ],
    'd3-format' => [
        'version' => '3.1.0',
    ],
    'd3-geo' => [
        'version' => '3.1.1',
    ],
    'd3-hierarchy' => [
        'version' => '3.1.2',
    ],
    'd3-interpolate' => [
        'version' => '3.0.1',
    ],
    'd3-path' => [
        'version' => '3.1.0',
    ],
    'd3-polygon' => [
        'version' => '3.0.1',
    ],
    'd3-quadtree' => [
        'version' => '3.0.1',
    ],
    'd3-random' => [
        'version' => '3.0.1',
    ],
    'd3-scale' => [
        'version' => '4.0.2',
    ],
    'd3-scale-chromatic' => [
        'version' => '3.1.0',
    ],
    'd3-selection' => [
        'version' => '3.0.0',
    ],
    'd3-shape' => [
        'version' => '3.2.0',
    ],
    'd3-time' => [
        'version' => '3.1.0',
    ],
    'd3-time-format' => [
        'version' => '4.1.0',
    ],
    'd3-timer' => [
        'version' => '3.0.1',
    ],
    'd3-transition' => [
        'version' => '3.0.1',
    ],
    'd3-zoom' => [
        'version' => '3.0.0',
    ],
    'dagre-d3-es/src/dagre/index.js' => [
        'version' => '7.0.14',
    ],
    'dagre-d3-es/src/graphlib/json.js' => [
        'version' => '7.0.14',
    ],
    'dagre-d3-es/src/graphlib/index.js' => [
        'version' => '7.0.14',
    ],
    'cytoscape' => [
        'version' => '3.33.1',
    ],
    'cytoscape-cose-bilkent' => [
        'version' => '4.1.0',
    ],
    '@mermaid-js/parser' => [
        'version' => '1.1.0',
    ],
    'dayjs/plugin/isoWeek.js' => [
        'version' => '1.11.20',
    ],
    'dayjs/plugin/customParseFormat.js' => [
        'version' => '1.11.20',
    ],
    'dayjs/plugin/advancedFormat.js' => [
        'version' => '1.11.20',
    ],
    'dayjs/plugin/duration.js' => [
        'version' => '1.11.20',
    ],
    'uuid' => [
        'version' => '11.1.0',
    ],
    'd3-sankey' => [
        'version' => '0.12.3',
    ],
    'lodash-es/clone.js' => [
        'version' => '4.17.23',
    ],
    'cytoscape-fcose' => [
        'version' => '2.2.0',
    ],
    '@upsetjs/venn.js' => [
        'version' => '2.0.0',
    ],
    'internmap' => [
        'version' => '2.0.3',
    ],
    'delaunator' => [
        'version' => '5.0.0',
    ],
    'lodash-es' => [
        'version' => '4.17.21',
    ],
    'cose-base' => [
        'version' => '2.2.0',
    ],
    'langium' => [
        'version' => '4.2.1',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/info-OMHHGYJF.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/packet-4T2RLAQJ.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/pie-ZZUOXDRM.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/treeView-SZITEDCU.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/architecture-YZFGNWBL.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/gitGraph-7Q5UKJZL.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/radar-PYXPWWZC.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/treemap-W4RFUUIX.mjs' => [
        'version' => '1.1.0',
    ],
    '@mermaid-js/parser/dist/chunks/mermaid-parser.core/wardley-RL74JXVD.mjs' => [
        'version' => '1.1.0',
    ],
    'robust-predicates' => [
        'version' => '3.0.0',
    ],
    'layout-base' => [
        'version' => '2.0.1',
    ],
    '@chevrotain/regexp-to-ast' => [
        'version' => '12.0.0',
    ],
    'chevrotain' => [
        'version' => '11.1.1',
    ],
    'chevrotain-allstar' => [
        'version' => '0.3.1',
    ],
    'vscode-languageserver-types' => [
        'version' => '3.17.5',
    ],
    'vscode-jsonrpc/lib/common/cancellation.js' => [
        'version' => '8.2.1',
    ],
    'vscode-languageserver-textdocument' => [
        'version' => '1.0.12',
    ],
    'vscode-uri' => [
        'version' => '3.1.0',
    ],
    'vscode-jsonrpc/lib/common/events.js' => [
        'version' => '8.2.1',
    ],
    'vscode-languageserver-protocol' => [
        'version' => '3.17.5',
    ],
    '@chevrotain/utils' => [
        'version' => '11.1.1',
    ],
    '@chevrotain/gast' => [
        'version' => '11.1.1',
    ],
    '@chevrotain/cst-dts-gen' => [
        'version' => '11.1.1',
    ],
    'lodash-es/map.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/filter.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/min.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/flatMap.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/uniqBy.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/flatten.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/forEach.js' => [
        'version' => '4.17.21',
    ],
    'lodash-es/reduce.js' => [
        'version' => '4.17.21',
    ],
    'vscode-jsonrpc/browser' => [
        'version' => '8.2.0',
    ],
    'vscode-jsonrpc' => [
        'version' => '8.2.0',
    ],
    'highlight.js' => [
        'version' => '11.11.1',
    ],
];
