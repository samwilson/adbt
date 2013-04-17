<?php

class ADBT_View_Database_Field extends ADBT_View_HTML
{

    /** @var ADBT_Model_Column The column this field represents. */
    protected $column;

    /** @var boolean Whether we're trying to edit this field. */
    protected $editing;

    /** @var boolean Whether we're allowed to edit this field. */
    protected $editable;

    /** @var string The readonly input attribute, with leading space.Empty if field should be writable. */
    protected $readonly;

    public function __construct($app, $column, $row, $form_field_name)
    {
        parent::__construct($app);
        $this->column = $column;
        $this->row = $row;
        $this->form_field_name = $form_field_name;
        $this->editable = $this->column->can('update') || $this->column->can('insert');
        $this->value = $this->row[$this->column->getName()];
        $this->readonly = !$this->editable ? ' readonly' : '';
    }

    public function setEditing($editing = false) {
        $this->editing = $editing === true;
    }

    public function setEditable($editable = false) {
        $this->editable = $editable === true;
    }

    public function output()
    {
        $type = ucwords($this->column->get_type());
        $action = $this->editing && $this->editable ? 'Edit' : 'View';
        $methodName = "output$type$action";
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $methodName = "outputVarchar$action";
            $this->$methodName();
        }
    }

    public function outputDateEdit()
    {
        echo '<input type="text" '.$this->readonly
            .'name="'.$this->form_field_name.'" '
            .'id="'.$this->form_field_name.'" '
            .'value="'.$this->value.'" '
            .'size="10" '
            .'class="datepicker" />';
    }

    public function outputDateView() {
        echo $this->value;
    }

    public function outputVarcharEdit()
    {
        ?>

        <?php if ($this->column->get_size() > 0 || $this->column->get_type() == 'text'): ?>

            <?php if ($this->column->get_size() > 0 && $this->column->get_size() < 150): ?>
                <input type="text" <?php echo $this->readonly ?>
                       name="<?php echo $this->form_field_name ?>"
                       value="<?php echo $this->value ?>" id="<?php echo $this->form_field_name ?>"
                       size="<?php echo min($this->column->get_size(), 35) ?>" />
            <?php else: ?>
                <textarea name="<?php echo $this->form_field_name ?>"
                          cols="35" rows="4" <?php echo $this->readonly ?>
                          id="<?php echo $this->form_field_name ?>"><?php echo $this->value ?></textarea>
            <?php endif ?>

        <?php else: ?>

            <input type="text" <?php echo $this->readonly ?>
                   name="<?php echo $this->form_field_name ?>" 
                   value="<?php echo $this->value ?>" id="<?php echo $this->form_field_name ?>" />

        <?php endif ?>

        <ul class="notes">

            <?php if ($this->column->get_comment()): ?>
            <li><strong><?php echo $this->column->get_comment() ?></strong></li>
            <?php endif ?>

            <?php if ($this->column->get_size() > 0): ?>
            <li>Maximum length: <?php echo $this->column->get_size() ?> characters.</li>
            <?php endif ?>

        </ul>

        <?php
    }

    public function outputVarcharView()
    {
        echo $this->value;
    }

    public function outputIntEdit()
    {

        /**
         * PK column
         */
        if ($this->column->isPrimaryKey())
        {

            echo '<input type="text" readonly value="'.$this->value.'" '
                 .'name="'.$this->form_field_name.'" '
                 .'id="'.$this->form_field_name.'" '
                 .'size="'.$this->column->get_size().'" />';

        /**
         * Booleans
         */
        } elseif ($this->column->get_size() == 1) {
            $checked = ($this->value == 1) ? 'checked' : '';
            echo '<input type="checkbox" '.$this->readonly.' '
                .'name="'.$this->form_field_name.'" '
                .'id="'.$this->form_field_name.'" '
                .$checked.' />';

        /**
         * Foreign keys
         */
        } elseif ($this->column->is_foreign_key()) {
            $this->outputIntEditFK();

        /**
         * Everything else
         */
        } else {
                $size = min(35, $this->column->get_size());
                echo '<input type="text" '.$this->readonly.' '
                    .'name="'.$this->form_field_name.'" '
                    .'id="'.$this->form_field_name.'" '
                    .'value="'.$this->value.'" '
                    .'size="'.$size.'" />';

        } /* end ifs choosing type of input. */

        if ($this->column->get_comment()) {
            echo '<ul class="notes">'
            . '<li><strong>' . $this->column->get_comment() . '</strong></li>'
            . '</ul>';
        }

    }

    public function outputIntEditFK() {
        $referenced_table = $this->column->get_referenced_table();
        $size = $referenced_table->count_records();
        if ($size < 100) {
            $this->outputIntEditFKsmall();
        } else {
            $this->outputIntEditFKbig();
        }
        ?>
        <ul class="notes">
            <li>
                This is a cross-reference to
                <?php
                $url = "database/index/" . $referenced_table->getName();
                $title = $this->titlecase($referenced_table->getName());
                ?>
                <a href="<?php echo $this->url($url) ?>"><?php echo $title ?></a>.
            </li>
            <?php if ($this->value): ?>
            <li>
                <?php
                $url = 'database/edit/'.$referenced_table->getName().'/'.$this->value;
                ?>
                <a href="<?php echo $this->url($url) ?>" title="">
                    View <?php echo $referenced_table->get_title($this->value) ?>
                </a>
                (<?php echo $this->titlecase($referenced_table->getName()) ?>
                record #<?php echo $this->value ?>).
            </li>
            <?php endif ?>
        </ul>
        <?php
    }

    public function outputIntEditFKsmall() {
        $referenced_table = $this->column->get_referenced_table();
        $rows = $referenced_table->getRows(false);
        $pk_name = $referenced_table->get_pk_column()->getName();
        $title_name = $referenced_table->get_title_column()->getName();
        echo '<select name="'.$this->form_field_name.'"'.$this->readonly.' >'."\n";
        if (!$this->column->is_required()) echo '<option></option>';
        foreach ($rows as $row) {
            $selected = ($row[$pk_name]==$this->value) ? ' selected' : '';
            echo '<option value="'.$row[$pk_name].'"'.$selected.'>';
            echo $row[$title_name].'</option>'."\n";
        }
        echo '</select>'."\n";
    }

    public function outputIntEditFKbig() {
        $referenced_table = $this->column->get_referenced_table();
        ?>

        <script type="text/javascript">
        <?php $fk_actual_value_field = str_replace('[', '\\[', str_replace(']', '\\]', $this->form_field_name)) ?>
        <?php $fk_field_name = str_replace('[', '_', str_replace(']', '_', $this->form_field_name)) . '_label' ?>
            $(function() {
                var fk_field_name = '<?php echo $fk_field_name ?>';
                $("[name='"+fk_field_name+"']").autocomplete({
                    source: "<?php echo $this->url('database/autocomplete/' . $referenced_table->getName()) ?>",
                    select: function(event, ui) {
                        var fk_actual_value_field = '<?php echo $fk_actual_value_field ?>';
                        $("[name='"+fk_actual_value_field+"']").val(ui.item.id);
                        return true;
                    },
                    change: function(event, ui) {
                        if ($(this).val().length==0) {
                            var fk_actual_value_field = '<?php echo $fk_actual_value_field ?>';
                            $("[name='"+fk_actual_value_field+"']").val('');
                            return true;
                        }
                    }
                });
            });
        </script>

        <?php $form_field_value = ($this->value > 0) ? $referenced_table->get_title($this->value) : '' ?>
        <input type="text" class="foreign-key-actual-value" readonly
               name="<?php echo $this->form_field_name ?>"
               size="<?php echo (empty($this->value)) ? 1 : strlen($this->value) ?>"
               value="<?php echo $this->value ?>" />
        <input type="text" class="foreign-key" <?php echo $this->readonly ?>
               name="<?php echo $fk_field_name ?>"
               size="30"
               value="<?php echo $form_field_value ?>" />
        <?php
    }

    public function outputIntView() {

        if ($this->column->is_foreign_key() && $this->value) {

            $referenced_table = $this->column->get_referenced_table();
            $url = "database/edit/" . $referenced_table->getName() . '/' . $this->value;
            $title = 'View record #'.$this->value.' in the '.$this->titlecase($referenced_table->getName()).' table.';
            echo '<a href="'.$this->url($url).'" title="'.$title.'">'
                .$referenced_table->get_title($this->value)
                .'</a>';

        } elseif ($this->column->get_size() == 1) {

            if ($this->value == 1) echo 'Yes';
            elseif ($this->value == 0) echo 'No';
            else echo '';

        } else {
            echo $this->value;
        }
    }

}