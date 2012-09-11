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
                <?php
                $pagination = $this->table->get_pagination();
                if ($first_page !== FALSE): ?>
                        <a href="<?php echo HTML::chars($page->url($first_page)) ?>" rel="first"><?php echo __('First') ?></a>
                <?php else: ?>
                        <?php echo __('First') ?>
                <?php endif ?>

                <?php if ($previous_page !== FALSE): ?>
                        <a href="<?php echo HTML::chars($page->url($previous_page)) ?>" rel="prev"><?php echo __('Previous') ?></a>
                <?php else: ?>
                        <?php echo __('Previous') ?>
                <?php endif ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                        <?php if ($i == $current_page): ?>
                                <strong><?php echo $i ?></strong>
                        <?php else: ?>
                                <a href="<?php echo HTML::chars($page->url($i)) ?>"><?php echo $i ?></a>
                        <?php endif ?>

                <?php endfor ?>

                <?php if ($next_page !== FALSE): ?>
                        <a href="<?php echo HTML::chars($page->url($next_page)) ?>" rel="next"><?php echo __('Next') ?></a>
                <?php else: ?>
                        <?php echo __('Next') ?>
                <?php endif ?>

                <?php if ($last_page !== FALSE): ?>
                        <a href="<?php echo HTML::chars($page->url($last_page)) ?>" rel="last"><?php echo __('Last') ?></a>
                <?php else: ?>
                        <?php echo __('Last') ?>
                <?php endif ?>
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
                                //print_r($row);
                                $pk_name = $this->table->get_pk_column()->getName();
                                $label = ($this->table->can('update')) ? 'Edit' : 'View';
                                $url = 'database/edit/' . $this->table->getName() . '/' . $row[$pk_name];
                                ?>
                                <a href="<?php echo $this->url($url) ?>">
                                    <?php echo $label ?>
                                </a>
                            </td>
                        <?php endif // if ($the_table->get_pk_column())  ?>

                        <?php foreach ($this->table->getColumns() as $column) { ?>
                            <td class="<?php echo $column->get_type() ?>-type <?php if ($column->is_foreign_key()) echo 'foreign-key' ?>">
                                <?php
                                //$form_field_name = 'data[' . $this->row[$pk_name] . '][' . $column->getName() . ']'
                                $field = new ADBT_View_Database_Field($column, $row, null);
                                $field->edit = false;
                                $field->output();
                                ?>
                            </td>
                        <?php } // foreach ($this->table->getColumns() as $column) ?>
                    </tr>
                <?php } // foreach ($rows as $row) ?>
            </tbody>
        </table>



        <?php
    }

}