<?php

namespace Modules\Core\Models;

/**
 * DocumentGroup model - Legacy compatibility layer.
 *
 * @deprecated Use Numbering model instead. This class is maintained for backward compatibility.
 *
 * This class extends Numbering to provide backward compatibility with code that still references DocumentGroup.
 * All new code should use the Numbering model directly.
 */
class DocumentGroup extends Numbering
{
    //
}
