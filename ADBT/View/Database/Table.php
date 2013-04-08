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

        <?php $rows = $this->table->getRows(true, true) ?>

        <div class="tableview">
        <table class="tableview">
            <caption>
                Found <?php echo number_format($this->table->count_records()) ?>
                record<?php if ($this->table->count_records() != 1) echo 's' ?>
                <br /><?php $this->outputPagination() ?>
            </caption>
            <thead>
                <tr>

                    <?php if ($this->table->get_pk_column()): ?>
                        <th>&nbsp;</th>
                    <?php endif ?>

                    <?php
                    foreach ($this->table->getColumns() as $column) {
                        $title = $this->titlecase($column->getName());
                        $orderdir = strtolower($this->table->getOrderDir());
                        $class = '';
                        if ($this->table->getOrderBy() == $column->getName()) {
                            $title .= "&nbsp;<img src='" . $this->url("site/resources/img/sort_$orderdir.png") . "' alt='Sort-direction icon' />";
                            $orderdir = ($orderdir == 'desc') ? 'asc' : 'desc';
                            $class = 'sorted';
                        }
                        $params = array('orderby'=>$column->getName(), 'orderdir'=>$orderdir);
                        $url = $this->url("/database/index/".$this->table->getName(), $params);
                        ?>
                        <th class="<?php echo $class ?>">
                            <a href="<?php echo $url ?>"><?php echo $title ?></a>
                        </th>
                    <?php } // foreach ($this->table->getColumns() as $column) ?>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>

                        <?php if ($this->table->get_pk_column()): ?>
                            <td>
                                <?php
                                $pk_name = $this->table->get_pk_column()->getName();
                                $label = ($this->table->can('update')) ? 'Edit' : 'View';
                                $url = 'database/edit/' . $this->table->getName() . '/' . $row[$pk_name];
                                ?>
                                <a href="<?php echo $this->url($url) ?>">
                                    <?php echo $label ?>
                                </a>
                            </td>
                        <?php endif // if ($the_table->get_pk_column())  ?>

                        <?php foreach ($this->table->getColumns() as $column): ?>
                            <td class="<?php echo $column->get_type() ?>-type
                                <?php if ($column->is_foreign_key()) { echo 'foreign-key'; } ?>">
                                <?php
                                //$form_field_name = 'data[' . $this->row[$pk_name] . '][' . $column->getName() . ']'
                                $field = new ADBT_View_Database_Field($this->app, $column, $row, null);
                                $field->edit = false;
                                $field->output();
                                ?>
                            </td>
                        <?php endforeach; // foreach ($this->table->getColumns() as $column) ?>
                    </tr>
                <?php endforeach; // foreach ($rows as $row) ?>
            </tbody>
        </table>

        <h4 class="debug">
            <a class="ui-icon ui-state-default ui-icon-gear" title="View debugging information">?</a>
        </h4>
        <dl class="debug" title="This table was produced from the following query and data">
            <?php $query = $this->table->get_saved_query() ?>
            <dt>SQL Query:</dt>
            <dd>
                <?php
                $sql = new ADBT_View_Database_SQL($query['sql']);
                $sql->output();
                ?>
            </dd>
            <?php if (count($query['parameters']) > 0): ?>
            <dt>Parameters:</dt>
            <dd>
                <ol>
                    <?php foreach ($query['parameters'] as $param): ?>
                    <li><code><?php echo $param ?></code></li>
                    <?php endforeach ?>
                </ol>
            </dd>
            <?php endif ?>
            <dt>Referenced Tables:</dt>
            <dd>
                <?php echo join('<br />', $this->table->get_referenced_tables()) ?>
            </dd>
            <dt>Defining SQL:</dt>
            <dd><pre><?php echo $this->table->get_defining_sql() ?></pre></dd>
        </dl>
        </div>

        <?php
    }

    public function outputPagination()
    {
        $page_count = $this->table->get_page_count();
        if ($page_count<=1) {
            return;
        }
        echo 'Page: ';
        $ellipsing = false;
        for ($page_num=1; $page_num<=$page_count; $page_num++) {
            if ($page_num==$this->table->page()) {
                echo " <strong>$page_num</strong> ";
                $ellipsing = false;
            } elseif ($page_num<5 || $page_num>$page_count-5) {
                $url = $this->url('/database/index/'.$this->table->getName().'?page='.$page_num);
                echo " <a href='$url' title='Go to page $page_num'>$page_num</a> ";
                $ellipsing = false;
            } elseif (!$ellipsing) {
                echo ' &hellip; ';
                $ellipsing = true;
            }
        }

    }
}
