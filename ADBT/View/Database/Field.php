<?php

class ADBT_View_Database_Field extends ADBT_View_HTML
{

    public function __construct($app, $column, $row, $form_field_name)
    {
        parent::__construct($app);
        $this->column = $column;
        $this->row = $row;
        $this->form_field_name = $form_field_name;
    }

    public function output()
    {
        $type = ucwords($this->column->get_type());
        $methodName = "output$type";
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->outputVarchar();
        }
    }

    public function outputDate()
    {
        $value = $this->row[$this->column->getName()];

        /**
         * Edit
         */
        if ($this->edit) {
            echo '<input type="text" '
                .'name="'.$this->form_field_name.'" '
                .'id="'.$this->form_field_name.'" '
                .'value="'.$value.'" '
                .'size="10" '
                .'class="datepicker" />';
        } else {
            echo $value;
        }
    }

    public function outputVarchar()
    {
        $colName = $this->column->getName();
        $value = $this->row[$colName];
        if (!$this->edit):
            echo $value;
        else:
            if ($this->column->get_size() > 0 || $this->column->get_type() == 'text'):

                if ($this->column->get_size() > 0 && $this->column->get_size() < 150):
                    ?>
                    <input type="text"
                           name="<?php echo $this->form_field_name ?>"
                           value="<?php echo $value ?>" id="<?php echo $this->form_field_name ?>"
                           size="<?php echo min($this->column->get_size(), 35) ?>" />
                           <?php
                       else:
                           ?>
                    <textarea name="<?php echo $this->form_field_name ?>" cols="35" rows="4"
                              id="<?php echo $this->form_field_name ?>"><?php echo $value ?></textarea>
                          <?php endif ?>

                <?php else: ?>
                    <input type="text"
                           name="<?php echo $this->form_field_name ?>"
                           value="<?php echo $value ?>" id="<?php echo $this->form_field_name ?>" />
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
        endif;
    }

    public function outputInt()
    {
        $value = $this->row[$this->column->getName()];

        /**
         * Edit
         */
        if ($this->edit):


            /**
             * PK column
             */
            if ($this->column->isPrimaryKey()):
                ?>
                <input type="text" readonly
                       value="<?php echo $value ?>"
                       name="<?php echo $this->form_field_name ?>"
                       id="<?php echo $this->form_field_name ?>"
                       size="<?php echo $this->column->get_size() ?>" />
                <?php

            /**
             * Booleans
             */
            elseif ($this->column->get_size() == 1):
                $checked = ($value==1) ? 'selected' : '';
                echo '<input type="checkbox" '
                    .'name="'.$this->form_field_name.'" '
                    .'id="'.$this->form_field_name.'" '
                    .$checked.' />';

            /**
             * Foreign keys
             */
            elseif ($this->column->is_foreign_key()):
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

                <?php $form_field_value = ($value > 0) ? $referenced_table->get_title($value) : '' ?>
                <input type="text" class="foreign-key-actual-value" readonly
                       name="<?php echo $this->form_field_name ?>"
                       size="<?php echo (empty($value)) ? 1 : strlen($value) ?>"
                       value="<?php echo $value ?>" />
                <input type="text" class="foreign-key"
                       name="<?php echo $fk_field_name ?>"
                       size="30"
                       value="<?php echo $form_field_value ?>" />
                <ul class="notes">
                    <li>
                        This is a cross-reference to
                        <?php
                        $url = "database/index/" . $referenced_table->getName();
                        $title = $this->titlecase($referenced_table->getName());
                        ?>
                        <a href="<?php echo $this->url($url) ?>"><?php echo $title ?></a>.
                    </li>
                        <?php if ($value): ?>
                        <li>
                            <?php
                            $url = 'database/edit/'.$referenced_table->getName().'/'.$value;
                            ?>
                            <a href="<?php echo $this->url($url) ?>" title="">
                                View <?php echo $referenced_table->get_title($value) ?>
                            </a>
                            (<?php echo $this->titlecase($referenced_table->getName()) ?>
                            record #<?php echo $value ?>).
                        </li>
                <?php endif ?>
                </ul>


                <?php
            /**
             * Everything else
             */
            else:
                $size = min(35, $this->column->get_size());
                echo '<input type="text" '
                    .'name="'.$this->form_field_name.'" '
                    .'id="'.$this->form_field_name.'" '
                    .'value="'.$value.'" '
                    .'size="'.$size.'" />';

            endif /* end ifs choosing type of input. */ ?>



            <?php
            if ($this->column->get_comment()) {
                echo '<ul class="notes">'
                . '<li><strong>' . $this->column->get_comment() . '</strong></li>'
                . '</ul>';
            }
            ?>



        <?php /**
         * Don't edit
         */ else: ?>

            <?php if ($this->column->is_foreign_key() && $value): ?>
                <?php
                $referenced_table = $this->column->get_referenced_table();
                $url = "database/edit/" . $referenced_table->getName() . '/' . $value;
                ?>
                <a href="<?php echo $this->url($url) ?>"
                   title="View record #<?php echo $value ?> in the <?php echo $this->titlecase($referenced_table->getName()) ?> table.">
                    <?php echo $referenced_table->get_title($value) ?>
                </a>

            <?php elseif ($this->column->get_size() == 1): ?>
                <?php if ($value == 1) echo 'Yes'; elseif ($value == 0) echo 'No'; else echo ''; ?>

            <?php else: ?>
                <?php echo $value ?>

            <?php endif ?>

        <?php
        endif;
    }

}