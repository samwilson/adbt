<?php

class ADBT_View_Database_Edit extends ADBT_View_Database_Base
{

    /** @var ADBT_Model_Table The table that's being edited. */
    public $table;

    public $row;

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * This should be called after $this->setId(), so it knows if we're editing
     * or updating.
     * 
     * @param ADBT_Model_Table $table The table
     */
    public function setTable($table)
    {
        $this->table = $table;
        $table_title = $this->titlecase($this->table->getName());
        if (!empty($this->id)) {
            $this->title = "Edit $table_title record $this->id";
        } else {
            $this->title = "Create new $table_title record";
        }
    }

    public function outputContent()
    {
        parent::outputContent();
        $column_names = array_values($this->table->getColumns());
        $num_cols = 3;
        ?>

        <script type="text/javascript">
            $(function() {
                $("input,textarea,select").focus(function(){
                    var this_id = $(this).attr('id');
                    $(this).parents('tr').find("label[for='"+this_id+"']").parents('th').attr('class', 'focused');
                    $(this).parents('td').attr('class', 'focused');
                });
                $("input,textarea,select").blur(function(){
                    var this_id = $(this).attr('id');
                    $(this).parents('tr').find("label[for='"+this_id+"']").parents('th').attr('class', '');
                    $(this).parents('td').attr('class', '');
                });
            });
        </script>

        <?php
        $url = 'database/edit/'.$this->table->getName();
        if (!empty($this->id)) $url .= '/'.$this->id;
        ?>
        <form action="<?php echo $this->url($url) ?>" method="post">
            <table class="edit-form">

                <?php for ($row_num = 0; $row_num < ceil(count($column_names)); $row_num++): ?>

                    <tr>
                        <?php
                        for ($col_num = 0; $col_num < $num_cols; $col_num++):
                            if (!isset($column_names[$row_num * $num_cols + $col_num])) {
                                continue;
                            }
                            $column = $column_names[$row_num * $num_cols + $col_num];
                            $pk_name = $this->table->get_pk_column()->getName();
                            $has_pk = isset($this->row[$pk_name]);
                            $pk_is_numeric = is_numeric($this->row[$pk_name]);
                            $form_field_name = ($has_pk && $pk_is_numeric)
                                ? 'data[' . $this->row[$pk_name] . '][' . $column->getName() . ']'
                                : 'data[new][' . $column->getName() . ']';
                            ?>
                        <th>
                            <label for="<?php echo $form_field_name ?>"
                                   title="Column type: <?php echo $column->get_type() ?>">
                                 <?php echo $this->titlecase($column->getName()) ?>
                            </label>
                        </th>
                        <td>
                            <?php
                            $field = new ADBT_View_Database_Field($column, $this->row, $form_field_name);
                            $field->edit = $column->can('update') || $column->can('insert');
                            $field->output();
                            ?>
                        </td>
                        <?php endfor // columns   ?>
                    </tr>

        <?php endfor // rows  ?>

                <?php if ($this->table->can('update') || $this->table->can('insert')) { ?>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo $num_cols * 2 ?>">
                                <input type="submit" name="save" value="Save" />
                            </td>
                        </tr>
                    </tfoot>
                <?php } // If can save? ?>
            </table>

        </form>



        <?php
        $related_tables = $this->table->get_referencing_tables();
        if (isset($this->row[$this->table->get_pk_column()->getName()]) && count($related_tables) > 0):
            ?>

                <div class="related-tables">
                    <h2>Related Records:</h2>
                    <ol>
            <?php
            foreach ($related_tables as $foreign) {
                $foreign_column = $foreign['column'];
                $foreign_table = $foreign['table'];
                $foreign_table->reset_filters();
                //$filter_value = $this->table->get_title($this->row[$this->table->get_pk_column()->getName()]);
                $filter_value = $this->row[$this->table->get_pk_column()->getName()];
                $foreign_table->addFilter($foreign_column, '=', $filter_value, true);
                $num_foreign_records = $foreign_table->count_records();
                $class = ($num_foreign_records > 0) ? '' : 'no-records';
                ?>
            <li>
                <h3 title="Show or hide these related records" class="anchor <?php echo $class ?>">
                    <?php echo $this->titlecase($foreign_table->getName()) ?>
                    <span class="smaller">(as &lsquo;<?php echo $this->titlecase($foreign_column) ?>&rsquo;).</span>
                    <?php echo number_format($num_foreign_records) ?> record<?php echo ($num_foreign_records != 1) ? 's' : '' ?>.
                </h3>
                    <div>
                        <p class="new-record">
                            <?php
                            $url = 'database/edit/' . $foreign_table->getName()
                                 . '?' . $foreign_column
                                 . '=' . $this->row[$this->table->get_pk_column()->getName()];
                            ?>
                            <a href="<?php echo $this->url($url) ?>">Add a new record here.</a>
                        </p>
                        <?php
                        $table_view = new ADBT_View_Database_Table();
                        $table_view->table = $foreign_table;
                        $table_view->output();
                        ?>
                        </div>
                    </li>
            <?php } // foreach ($related_tables as $foreign) ?>
                    </ol>
                </div>

            <?php
        endif;
    }

}