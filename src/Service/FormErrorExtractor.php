<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\FormInterface;

class FormErrorExtractor
{
    /**
     * Returns form errors keyed by field name (camelCase, no form prefix).
     *
     * @return array<string, string[]>
     */
    public function extract(FormInterface $form): array
    {
        $errors = [];

        foreach ($form as $name => $child) {
            $fieldErrors = [];
            foreach ($child->getErrors() as $error) {
                $fieldErrors[] = $error->getMessage();
            }
            if ([] !== $fieldErrors) {
                $errors[$name] = $fieldErrors;
            }
        }

        $formErrors = [];
        foreach ($form->getErrors() as $error) {
            $formErrors[] = $error->getMessage();
        }
        if ([] !== $formErrors) {
            $errors['_form'] = $formErrors;
        }

        return $errors;
    }
}
