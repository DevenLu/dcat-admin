<?php

namespace Dcat\Admin\Form\Field;

use Dcat\Admin\Form\Field;

class Text extends Field
{
    use PlainInput;

    /**
     * Render this filed.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $this->initPlainInput();

        $this->prepend('<i class="ti-pencil"></i>')
            ->defaultAttribute('type', 'text')
            ->defaultAttribute('id', $this->id)
            ->defaultAttribute('name', $this->getElementName())
            ->defaultAttribute('value', old($this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->defaultAttribute('placeholder', $this->getPlaceholder());

        $this->addVariables([
            'prepend' => $this->prepend,
            'append'  => $this->append,
        ]);

        return parent::render();
    }

    /**
     * Set input type.
     *
     * @param string $type
     * @return $this
     */
    public function type(string $type)
    {
        return $this->attribute('type', $type);
    }

    /**
     * Set "data-match" attribute.
     *
     * @see http://1000hz.github.io/bootstrap-validator/
     *
     * @param string $field
     * @param string $error
     * @return $this
     */
    public function confirm(string $field, ?string $error = null, ?string $fieldSelector = null)
    {
        if (! $fieldSelector && $this->form) {
            $fieldSelector = '#'.$this->form->field($field)->getElementId();
        }

        $attributes = [
            'data-match'       => $fieldSelector,
            'data-match-error' => str_replace(':attribute', $field, $error ?: trans('admin.validation.match'))
        ];

        return $this->attribute($attributes);
    }

    /**
     * Set error messages for individual form field.
     *
     * @see http://1000hz.github.io/bootstrap-validator/
     *
     * @param string $error
     * @return $this
     */
    public function validationError(string $error)
    {
        return $this->attribute('data-error', $error);
    }

    /**
     * Add inputmask to an elements.
     *
     * @param array $options
     *
     * @return $this
     */
    public function inputmask($options)
    {
        $options = $this->jsonEncodeOptions($options);

        $this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";

        return $this;
    }

    /**
     * Encode options to Json.
     *
     * @param array $options
     *
     * @return $json
     */
    protected function jsonEncodeOptions($options)
    {
        $data = $this->prepareOptions($options);

        $json = json_encode($data['options']);

        $json = str_replace($data['toReplace'], $data['original'], $json);

        return $json;
    }

    /**
     * Prepare options.
     *
     * @param array $options
     *
     * @return array
     */
    protected function prepareOptions($options)
    {
        $original = [];
        $toReplace = [];

        foreach ($options as $key => &$value) {
            if (is_array($value)) {
                $subArray = $this->prepareOptions($value);
                $value = $subArray['options'];
                $original = array_merge($original, $subArray['original']);
                $toReplace = array_merge($toReplace, $subArray['toReplace']);
            } elseif (preg_match('/function.*?/', $value)) {
                $original[] = $value;
                $value = "%{$key}%";
                $toReplace[] = "\"{$value}\"";
            }
        }

        return compact('original', 'toReplace', 'options');
    }

    /**
     * Add datalist element to Text input.
     *
     * @param array $entries
     *
     * @return $this
     */
    public function datalist($entries = [])
    {
        $this->defaultAttribute('list', "list-{$this->id}");

        $datalist = "<datalist id=\"list-{$this->id}\">";
        foreach ($entries as $k => $v) {
            $datalist .= "<option value=\"{$k}\">{$v}</option>";
        }
        $datalist .= '</datalist>';

        return $this->append($datalist);
    }
}
