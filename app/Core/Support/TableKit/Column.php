<?php

namespace App\Core\Support\TableKit;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use function e;
use function view;

class Column
{
    public const TYPE_TEXT = 'text';
    public const TYPE_NUMBER = 'number';
    public const TYPE_MONEY = 'money';
    public const TYPE_BADGE = 'badge';
    public const TYPE_DATE = 'date';
    public const TYPE_ENUM = 'enum';
    public const TYPE_ACTIONS = 'actions';

    /**
     * @var array<string, string>
     */
    protected const TYPE_DEFAULTS = [
        self::TYPE_TEXT => 'text',
        self::TYPE_NUMBER => 'number',
        self::TYPE_MONEY => 'number',
        self::TYPE_BADGE => 'text',
        self::TYPE_DATE => 'date',
        self::TYPE_ENUM => 'text',
        self::TYPE_ACTIONS => 'actions',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $options;

    public function __construct(
        protected string $key,
        protected string $label,
        protected string $type = self::TYPE_TEXT,
        protected bool $sortable = false,
        protected bool $filterable = false,
        protected bool $hiddenXs = false,
        protected ?array $enum = null,
        protected ?string $formatter = null,
        array $options = []
    ) {
        $this->options = $options;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function fromArray(array $definition): self
    {
        $key = (string) Arr::get($definition, 'key');
        $label = (string) Arr::get($definition, 'label', Str::title($key));
        $type = (string) Arr::get($definition, 'type', self::TYPE_TEXT);

        return new self(
            $key,
            $label,
            $type,
            (bool) Arr::get($definition, 'sortable', false),
            (bool) Arr::get($definition, 'filterable', false),
            (bool) Arr::get($definition, 'hidden_xs', false),
            Arr::get($definition, 'enum'),
            Arr::get($definition, 'formatter'),
            Arr::get($definition, 'options', [])
        );
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sortable(): bool
    {
        return $this->sortable && $this->type !== self::TYPE_ACTIONS;
    }

    public function filterable(): bool
    {
        return $this->filterable && $this->type !== self::TYPE_ACTIONS;
    }

    public function hiddenOnXs(): bool
    {
        return $this->hiddenXs;
    }

    public function enum(): ?array
    {
        return $this->enum;
    }

    public function formatter(): ?string
    {
        return $this->formatter;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @param  array<string, mixed>|object  $row
     * @return array{raw:mixed, display:string, html:string}
     */
    public function prepareCell(array|object $row, mixed $cell = null): array
    {
        if ($cell === null) {
            $cell = $this->extractCell($row);
        }

        if (is_array($cell) && array_key_exists('html', $cell) && array_key_exists('display', $cell) && array_key_exists('raw', $cell)) {
            return [
                'raw' => $cell['raw'],
                'display' => (string) $cell['display'],
                'html' => (string) $cell['html'],
            ];
        }

        $raw = $this->determineRawValue($cell);

        $formatted = $this->applyFormatter($row, $raw, $cell);

        if (is_array($formatted) && isset($formatted['html'], $formatted['display'])) {
            return [
                'raw' => $formatted['raw'] ?? $raw,
                'display' => (string) $formatted['display'],
                'html' => (string) $formatted['html'],
            ];
        }

        $display = is_array($formatted) && isset($formatted['display'])
            ? (string) $formatted['display']
            : (string) $formatted;

        $html = is_array($formatted) && isset($formatted['html'])
            ? (string) $formatted['html']
            : e($display);

        return [
            'raw' => $raw,
            'display' => $display,
            'html' => $html,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontendDefinition(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'sortable' => $this->sortable(),
            'filterable' => $this->filterable(),
            'hiddenXs' => $this->hiddenOnXs(),
            'enum' => $this->enum,
            'options' => $this->options,
        ];
    }

    protected function extractCell(array|object $row): mixed
    {
        if (is_array($row) && array_key_exists($this->key, $row)) {
            return $row[$this->key];
        }

        if (is_object($row) && isset($row->{$this->key})) {
            return $row->{$this->key};
        }

        return null;
    }

    protected function determineRawValue(mixed $cell): mixed
    {
        if (is_array($cell)) {
            if (array_key_exists('raw', $cell)) {
                return $cell['raw'];
            }

            if (array_key_exists('value', $cell)) {
                return $cell['value'];
            }
        }

        return $cell;
    }

    protected function applyFormatter(array|object $row, mixed $raw, mixed $cell): mixed
    {
        if ($this->formatter) {
            if (is_callable($this->formatter)) {
                return call_user_func($this->formatter, $row, $this, $raw, $cell);
            }

            if (is_string($this->formatter) && function_exists($this->formatter)) {
                return call_user_func($this->formatter, $row, $this, $raw, $cell);
            }

            if (is_string($this->formatter) && str_contains($this->formatter, '@')) {
                return app()->call($this->formatter, compact('row', 'raw', 'cell'));
            }

            if (is_string($this->formatter) && str_contains($this->formatter, '::')) {
                return call_user_func($this->formatter, $row, $this, $raw, $cell);
            }
        }

        return $this->defaultFormatter($raw);
    }

    protected function defaultFormatter(mixed $raw): string|array
    {
        return match ($this->type) {
            self::TYPE_NUMBER => $this->formatNumber($raw),
            self::TYPE_MONEY => $this->formatMoney($raw),
            self::TYPE_BADGE => [
                'raw' => $raw,
                'display' => $this->resolveBadgeLabel($raw),
                'html' => sprintf('<span class="tablekit__badge tablekit__badge--%s">%s</span>', e($this->normalizeBadgeModifier($raw)), e($this->resolveBadgeLabel($raw))),
            ],
            self::TYPE_DATE => $this->formatDate($raw),
            self::TYPE_ACTIONS => $this->formatActions($raw),
            default => $this->formatText($raw),
        };
    }

    protected function formatText(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '—';
        }

        return (string) $raw;
    }

    protected function formatNumber(mixed $raw): string
    {
        if (! is_numeric($raw)) {
            return $this->formatText($raw);
        }

        $precision = (int) Arr::get($this->options, 'precision', 0);

        return number_format((float) $raw, $precision, ',', '.');
    }

    protected function formatMoney(mixed $raw): array|string
    {
        if (is_array($raw)) {
            $amount = Arr::get($raw, 'amount');
            $currency = Arr::get($raw, 'currency', Arr::get($this->options, 'currency'));

            if (is_numeric($amount)) {
                $formatted = number_format((float) $amount, (int) Arr::get($this->options, 'precision', 2), ',', '.');

                if ($currency) {
                    $formatted .= ' ' . $currency;
                }

                return [
                    'raw' => $amount,
                    'display' => $formatted,
                    'html' => e($formatted),
                ];
            }
        }

        if (is_numeric($raw)) {
            $formatted = number_format((float) $raw, (int) Arr::get($this->options, 'precision', 2), ',', '.');

            return [
                'raw' => (float) $raw,
                'display' => $formatted,
                'html' => e($formatted),
            ];
        }

        return [
            'raw' => $raw,
            'display' => (string) $raw,
            'html' => e((string) $raw),
        ];
    }

    protected function resolveBadgeLabel(mixed $raw): string
    {
        $value = is_string($raw) ? $raw : (string) $raw;

        if ($this->enum && array_key_exists($value, $this->enum)) {
            return (string) $this->enum[$value];
        }

        return Str::headline($value);
    }

    protected function normalizeBadgeModifier(mixed $raw): string
    {
        $value = is_string($raw) ? $raw : (string) $raw;

        return Str::slug($value);
    }

    protected function formatDate(mixed $raw): array|string
    {
        $format = (string) Arr::get($this->options, 'format', 'd.m.Y H:i');

        if ($raw instanceof Carbon) {
            $value = $raw->copy();
        } elseif (is_string($raw)) {
            $value = Carbon::parse($raw);
        } else {
            $value = null;
        }

        if ($value instanceof Carbon) {
            $formatted = $value->format($format);

            return [
                'raw' => $value->toIso8601String(),
                'display' => $formatted,
                'html' => e($formatted),
            ];
        }

        return [
            'raw' => $raw,
            'display' => $this->formatText($raw),
            'html' => e($this->formatText($raw)),
        ];
    }

    protected function formatActions(mixed $raw): array|string
    {
        $actions = [];

        if (is_array($raw)) {
            $actions = $raw;
        }

        $html = view('components.tablekit.row-actions', ['actions' => $actions])->render();

        return [
            'raw' => $actions,
            'display' => strip_tags($html),
            'html' => $html,
        ];
    }
}
