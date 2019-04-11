$(document).ready(function() 
{
var lastSearch = '';
UpdateButtons();

function UpdateButtons()
{
  var count = 0;
  $.each($("input[class='user']:checked"), function()
  {
    count++;
  });

  if(count == 0)
  {
    $('button#ban').prop("disabled",true);
    $('button#unban').prop("disabled",true);
    $('button#delete').prop("disabled",true);
  }
  else
  {
    $('button#ban').prop("disabled",false);
    $('button#unban').prop("disabled",false);
    $('button#delete').prop("disabled",false);
  }

  if(count == 1)
  {
    $('button#newpass').prop("disabled",false);
    $('button#setdiscord').prop("disabled",false);
  }
  else
  {
    $('button#newpass').prop("disabled",true);
    $('button#setdiscord').prop("disabled",true);

    if($('.pop').css('display') == 'block')
    {
      $('.pop').toggle();
    }

    if($('.discord').css('display') == 'block')
    {
      $('.discord').toggle();
    }
  }
}

function SearchUsers(text)
{
  $.post('ajax/searchusers.php', {textbox: text}, function(data) 
  {
    var jd = $.parseJSON(data);

    $('table.t').empty();
    $('table.t').append("<tr>\
      <th width=\"50\">Select</th>\
      <th>E-mail</th>\
      <th>Discord</th>\
      <th>Admin</th>\
      <th>Banned</th>\
      </tr>");
    $.each(jd, function(key, value) 
    {
      var row = "<tr>";
      $.each(value, function(key, value) 
      {
        if(key == "id")
        {
          row += "<td style=\"text-align: center; vertical-align: middle;\"><input type=\"checkbox\" class=\"user\" id=\""+value+"\"></input></td>";
        }
        else
        {
          row += "<td>"+value+"</td>";
        }
      });
      row += "</tr>";

      $('table.t').append(row);
      UpdateButtons();
    });
  });
}

$('button#search').click(function()
{
  var text = $('input#textbox').val();
  lastSearch = text;
  SearchUsers(text);
  return false;
});

$('input#textbox').on('keypress', function(e) 
{
  var code = e.keyCode || e.which;
  if(code==13)
  {
      $('button#search').click();
      return false;
  }
  return true;
});

$(document).change("input[name='checkbox']", function () 
{
  UpdateButtons();
  return false;
});

$('button#ban').click(function()
{
  $.each($("input[class='user']:checked"), function()
  {         
    var id = $(this).attr('id');

    $.post('ajax/banuser.php', {user: id, ban: '1'}, function(data) 
    {
      SearchUsers(lastSearch);
    });
  });
  return false;
});

$('button#unban').click(function()
{
  $.each($("input[class='user']:checked"), function()
  {         
    var id = $(this).attr('id');

    $.post('ajax/banuser.php', {user: id, ban: '0'}, function(data) 
    {
      SearchUsers(lastSearch);
    });
  });
  return false;
});

$('button#delete').click(function()
{
  $.each($("input[class='user']:checked"), function()
  {         
    var id = $(this).attr('id');

    $.post('ajax/deleteuser.php', {user: id}, function(data) 
    {
      SearchUsers(lastSearch);
    });
  });

  return false;
});

$('button#newpass').click(function()
{
  $("input#password_input").val("");
  $('h3.login_output').empty();
  $('.pop').toggle();

  if($('.discord').css('display') == 'block')
  {
    $('.discord').toggle();
  }
  return false;
});

$('input#password_input').on('keypress', function(e) 
{
  var code = e.keyCode || e.which;
  if(code==13)
  {
      $('button#password_submit').click();
      return false;
  }
  return true;
});

$('button#password_submit').click(function()
{
  $.each($("input[class='user']:checked"), function()
  {         
    var id   = $(this).attr('id');
    var text = $('input#password_input').val();

    $.post('ajax/newpassword.php', {user: id, password: text}, function(data) 
    {
      $('h3.login_output').empty();
      $('h3.login_output').append(data);
    });
  });
  return false;
});

$('.close').on('click', function() 
{
  $('.pop').toggle();
  return false;
});

$('button#setdiscord').click(function()
{
  $("input#discord_input").val("");
  $("h3.discord_output").empty();
  $('.discord').toggle();

  if($('.pop').css('display') == 'block')
  {
    $('.pop').toggle();
  }
  return false;
});

$('input#discord_input').on('keypress', function(e) 
{
  var code = e.keyCode || e.which;
  if(code==13)
  {
      $('button#discord_submit').click();
      return false;
  }
  return true;
});

$('button#discord_submit').click(function()
{
  $.each($("input[class='user']:checked"), function()
  {         
    var id   = $(this).attr('id');
    var text = $('input#discord_input').val();

    $.post('ajax/newdiscord.php', {user: id, discord: text}, function(data) 
    {
      SearchUsers(lastSearch);
    });
  });
  return false;
});

$('.discord_close').on('click', function() 
{
  $('.discord').toggle();
  return false;
});
});