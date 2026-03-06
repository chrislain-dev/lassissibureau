<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('products.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Récupérer l'ID depuis l'URL
        $productModelId = $this->route('product_model');

        // Si c'est un objet, prendre son ID, sinon c'est déjà l'ID
        if (is_object($productModelId)) {
            $productModelId = $productModelId->id;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('product_models', 'name')->ignore($productModelId),
                ],
            ],
            'brand' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', 'string', Rule::in(['telephone', 'tablette', 'pc', 'accessoire'])],
            'condition_type' => ['required', 'string', Rule::in(['neuf', 'venu', 'occasion'])],
            'image_url' => ['nullable', 'url', 'max:500'],
            'prix_revient_default' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'prix_vente_default' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:prix_revient_default'],
            'prix_vente_revendeur' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'gte:prix_revient_default'],
            'stock_minimum' => ['required', 'integer', 'min:0', 'max:1000'],
            'quantity'      => ['nullable', 'integer', 'min:0', 'max:99999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom du modèle',
            'brand' => 'marque',
            'description' => 'description',
            'category' => 'catégorie',
            'condition_type' => 'type de boîte',
            'image_url' => 'URL de l\'image',
            'prix_revient_default' => 'prix de revient par défaut',
            'prix_vente_default' => 'prix de vente par défaut',
            'prix_vente_revendeur' => 'prix de vente revendeur',
            'stock_minimum' => 'stock minimum',
            'quantity'      => 'quantité en stock',
            'is_active' => 'actif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du modèle est obligatoire.',
            'brand.required' => 'La marque est obligatoire.',
            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'La catégorie doit être "telephone", "tablette", "pc" ou "accessoire".',
            'condition_type.required' => 'Le type de boîte est obligatoire.',
            'condition_type.in' => 'Le type de boîte doit être "neuf", "venu" ou "occasion".',
            'prix_vente_default.gte' => 'Le prix de vente client doit être supérieur ou égal au prix de revient.',
            'prix_vente_revendeur.gte' => 'Le prix de vente revendeur doit être supérieur ou égal au prix de revient.',
            'stock_minimum.required' => 'Le stock minimum est obligatoire.',
            'quantity.integer'        => 'La quantité doit être un nombre entier.',
            'quantity.min'            => 'La quantité ne peut pas être négative.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('brand')) {
            $this->merge([
                'brand' => trim($this->brand),
            ]);
        }
    }
}
