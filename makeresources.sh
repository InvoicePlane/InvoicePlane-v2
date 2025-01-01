#!/bin/bash

# Define the components and their corresponding paths
components=(
  "CreateEmailTemplate:Modules/Core/Filament/Resources/EmailTemplateResource/Pages"
  "EditEmailTemplate:Modules/Core/Filament/Resources/EmailTemplateResource/Pages"
  "CreateTaxRate:Modules/Core/Filament/Resources/TaxRateResource/Pages"
  "EditTaxRate:Modules/Core/Filament/Resources/TaxRateResource/Pages"
  "CreateQuote:Modules/Quotes/Filament/Resources/QuoteResource/Pages"
  "EditQuote:Modules/Quotes/Filament/Resources/QuoteResource/Pages"
  "CreateProject:Modules/Projects/Filament/Resources/ProjectResource/Pages"
  "EditProject:Modules/Projects/Filament/Resources/ProjectResource/Pages"
  "CreateTask:Modules/Projects/Filament/Resources/TaskResource/Pages"
  "EditTask:Modules/Projects/Filament/Resources/TaskResource/Pages"
  "CreatePayment:Modules/Payments/Filament/Resources/PaymentResource/Pages"
  "EditPayment:Modules/Payments/Filament/Resources/PaymentResource/Pages"
)

# Iterate over the components and create files
for component in "${components[@]}"; do
  IFS=":" read -r name path <<< "$component"

  # Create the directory if it doesn't exist
  mkdir -p "$path"

  # Generate the PHP class
  cat > "$path/$name.php" <<EOL
<?php

namespace ${path//\//\\};

use Filament\Resources\Pages\Page;

class $name extends Page
{
EOL

  # Add the `save` method for Edit pages
  if [[ "$name" == Edit* ]]; then
    cat >> "$path/$name.php" <<EOL

    public function save(bool \$shouldRedirect = true, bool \$shouldSendSavedNotification = true): void
    {
        \$this->form->fill(array_merge(
            \$this->form->getRawState(),
            [
                'client_date_modified' => now()->toDateTimeString(),
            ]
        ));

        parent::save();
    }
EOL
  fi

  # Close the PHP class
  echo "}" >> "$path/$name.php"

  echo "Created $path/$name.php"
done
