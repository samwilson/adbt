<?php

class ADBT_View_Database_Table extends ADBT_View_HTML
{

    /** @var ADBT_Model_Table */
    public $table;

    public function output()
    {
        if (!$this->table)
            return;
        ?>

        <?php $rows = $this->table->getRows() ?>

        <table>
            <caption>
                Found <?php echo number_format($this->table->count_records()) ?>
                record<?php if ($this->table->count_records() != 1) echo 's' ?>
                <?php //echo $this->table->get_pagination()->render('pagination/floating') ?>
            </caption>
            <thead>
                <tr>

                    <?php if ($this->table->get_pk_column()): ?>
                        <th>&nbsp;</th>
                    <?php endif ?>

                    <?php
                    foreach ($this->table->getColumns() as $column) {
                        $title = $this->titlecase($column->getName());
                        $orderdir = $this->table->getOrderDir();
                        $class = '';
                        if ($this->table->getOrderBy() == $column->getName()) {
                            $title .= "&nbsp;<img src='" . $this->url("resources/img/sort_$orderdir.png") . "' alt='Sort-direction icon' />";
                            $orderdir = ($orderdir == 'desc') ? 'asc' : 'desc';
                            $class = 'sorted';
                        }
                        ?>
                        <th class="<?php echo $class ?>">
                            <a href="<?php echo $this->url("/database/index/".$this->table->getName()) ?>">
                                <?php echo $title ?>
                            </a>
                        </th>
                        <!-- $url = URL::query(array('orderby' => $column->getName(), 'orderdir' => $orderdir)); -->
                        <?php
                    }
                    ?>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) { ?>
                    <tr>

                        <?php if ($this->table->get_pk_column()): ?>
                            <td>
                                <?php
                                $pk_name = $this->table->get_pk_column()->getName();
                                $label = ($this->table->can('update')) ? 'Edit' : 'View';
                                $url = 'edit/' . $database->get_name() . '/' . $this->table->get_name() . '/' . $row[$pk_name];
                                echo html::anchor($url, $label);
                                ?>
                            </td>
                        <?php endif // if ($the_table->get_pk_column())  ?>

                        <?php foreach ($this->table->getColumns() as $column): ?>
                            <td class="<?php echo $column->get_type() ?>">
                                <?php
                                $edit = FALSE;
                                $form_field_name = '';
                                echo View::factory('field')
                                        ->bind('column', $column)
                                        ->bind('row', $row)
                                        ->bind('edit', $edit)
                                        ->bind('form_field_name', $form_field_name)
                                        ->render()
                                ?>
                            </td>
                        <?php endforeach ?>
                    </tr>
                <?php } // foreach ($rows as $row) ?>
            </tbody>
        </table>



        <?php
    }

}